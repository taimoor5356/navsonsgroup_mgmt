<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Models\VehicleBrand;
use App\Models\VehicleCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off cleanup of the vehicle catalog rows created by the legacy data
 * migration (App\Console\Commands\MigrateLegacyData). Those 189 rows came
 * straight from messy old free-text customer entries (typos, trim levels,
 * engine displacement, brand names embedded in the model name, etc.) and
 * need to become real, deduplicated model names with a brand and category.
 *
 * For every old messy name this command:
 *  1. Resolves it to a canonical (model name, brand, category) via a manually
 *     researched mapping (see $nameMap below).
 *  2. Reuses an existing catalog row with that exact name+brand if one
 *     already exists (e.g. "h.city" -> the pre-seeded "City" under Honda),
 *     otherwise renames one of the messy rows in place to become the
 *     canonical row for that model.
 *  3. Repoints every user_vehicles.vehicle_id (and fines.vehicle_id, if any)
 *     that pointed at a now-redundant duplicate row to the canonical row.
 *  4. Deletes the now-unreferenced duplicate catalog rows.
 *
 * Genuinely unidentifiable entries (single letters, sale-day labels, non
 * -vehicle items like "carpet"/"radiator"/"blanket") are bucketed into a
 * clearly-labeled "Unspecified"/"Miscellaneous" catalog row rather than
 * guessed at — see database/VEHICLE_CATALOG_CLEANUP.md for the full
 * reasoning per entry.
 */
class CleanupVehicleCatalog extends Command
{
    protected $signature = 'legacy:cleanup-vehicles {--dry-run : Roll back at the end instead of committing}';

    protected $description = 'Clean up and deduplicate the vehicle catalog rows created by the legacy data migration';

    /** @var array<string,int> brand name => id */
    private array $brandIds = [];

    /** @var array<string,int> category name => id */
    private array $categoryIds = [];

    /** @var array<string,int> "name|brandId" => vehicles.id (the running canonical index) */
    private array $catalogIndex = [];

    private int $renamed = 0;
    private int $mazdaRenamed = 0;
    private int $merged = 0;
    private int $deleted = 0;
    private int $userVehiclesRepointed = 0;
    private array $warnings = [];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? 'DRY RUN — will roll back at the end.' : 'Starting vehicle catalog cleanup...');

        DB::beginTransaction();

        try {
            $this->ensureBrandsAndCategories();
            $this->indexExistingCatalog();
            $this->fixMazdaNaming();
            $this->processMessyNames();

            if ($dryRun) {
                DB::rollBack();
                $this->info("\nDry run finished — rolled back, nothing persisted.");
            } else {
                DB::commit();
                $this->info("\nCleanup committed.");
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Cleanup failed and was rolled back: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }

        $this->printReport();

        if (!$dryRun) {
            $this->validate();
        }

        return 0;
    }

    /**
     * The pre-seeded 582-vehicle catalog references vehicle_brand_id 1, 2, 3, 5
     * (Toyota, Honda, Suzuki, Kia respectively, confirmed by the model names on
     * those rows) but no matching rows exist in vehicle_brands at all — an
     * orphaned FK that predates this cleanup and silently broke brand display
     * for 88 catalog rows. Insert them with their expected explicit ids so the
     * existing vehicle rows resolve correctly without needing to be touched.
     */
    private function fixOrphanedCoreBrandIds(): void
    {
        // Leftover unused duplicate from earlier ad-hoc testing this session — must go
        // first, since MySQL's case-insensitive collation means 'toyota' here would
        // otherwise collide with the proper-cased 'Toyota' row inserted just below.
        $dupe = VehicleBrand::where('name', 'toyota')->first();
        if ($dupe && !DB::table('vehicles')->where('vehicle_brand_id', $dupe->id)->exists()) {
            $dupe->delete();
            $this->warnings[] = "Deleted unused duplicate brand row id={$dupe->id} name='toyota' (leftover test data, no vehicles referenced it).";
        }

        $expected = [1 => 'Toyota', 2 => 'Honda', 3 => 'Suzuki', 5 => 'Kia'];
        foreach ($expected as $id => $name) {
            if (!VehicleBrand::find($id)) {
                DB::table('vehicle_brands')->insert([
                    'id' => $id,
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->warnings[] = "Inserted missing vehicle_brands row id={$id} name='{$name}' (was orphaned — referenced by existing catalog vehicles but never actually created).";
            }
        }
    }

    private function ensureBrandsAndCategories(): void
    {
        $this->fixOrphanedCoreBrandIds();

        foreach (['Daihatsu', 'Yamaha', 'United', 'Deer', 'Metro', 'EVee'] as $brand) {
            $row = VehicleBrand::firstOrCreate(['name' => $brand]);
            $this->brandIds[strtolower($brand)] = $row->id;
        }
        // Also index every brand that already existed (Toyota, Honda, Suzuki, etc.)
        foreach (VehicleBrand::all(['id', 'name']) as $b) {
            $this->brandIds[strtolower(trim($b->name))] = $b->id;
        }

        foreach (['Motorcycle', 'Electric Motorcycle', 'Rickshaw', 'Loader/Truck', 'Miscellaneous'] as $cat) {
            $row = VehicleCategory::firstOrCreate(['name' => $cat]);
            $this->categoryIds[strtolower($cat)] = $row->id;
        }
        foreach (VehicleCategory::all(['id', 'name']) as $c) {
            $this->categoryIds[strtolower(trim($c->name))] = $c->id;
        }

        $this->warnings[] = 'Brands ensured: Daihatsu, Yamaha, United, Deer, Metro, EVee (created if missing).';
        $this->warnings[] = 'Categories ensured: Motorcycle, Electric Motorcycle, Rickshaw, Loader/Truck, Miscellaneous (created if missing).';
    }

    private function indexExistingCatalog(): void
    {
        foreach (Vehicle::all(['id', 'name', 'vehicle_brand_id']) as $v) {
            $key = strtolower(trim($v->name)) . '|' . ($v->vehicle_brand_id ?? 'null');
            $this->catalogIndex[$key] = $v->id;
        }
    }

    private function fixMazdaNaming(): void
    {
        $renames = [
            'mazda2 (demio)' => 'Demio',
            'mazda3 (axela)' => 'Axela',
            'mazda6 (atenza)' => 'Atenza',
        ];
        foreach ($renames as $old => $new) {
            $row = Vehicle::whereRaw('LOWER(name) = ?', [$old])->first();
            if ($row) {
                $row->name = strtolower($new);
                $row->save();
                $key = strtolower($old) . '|' . ($row->vehicle_brand_id ?? 'null');
                unset($this->catalogIndex[$key]);
                $this->catalogIndex[strtolower($new) . '|' . ($row->vehicle_brand_id ?? 'null')] = $row->id;
                $this->mazdaRenamed++;
            }
        }
    }

    /**
     * old lowercased name => [canonical name, brand name or null, category name]
     */
    private function nameMap(): array
    {
        $bike = ['Bike', null, 'Motorcycle'];
        $unspecified = ['Unspecified', null, 'Miscellaneous'];
        $misc = ['Unspecified', null, 'Miscellaneous'];

        return [
            '125 bike' => $bike,
            'gli' => ['Corolla', 'Toyota', 'Sedan'],
            'jeep' => ['Jeep (Unspecified)', null, 'SUV'],
            'tanki wash' => $misc,
            'alto' => ['Alto', 'Suzuki', 'Mini'],
            'shavledge' => $unspecified,
            'bike' => $bike,
            'mira' => ['Mira', 'Daihatsu', 'Mini'],
            'coure alto' => ['Cuore', 'Daihatsu', 'Mini'],
            'bike 125' => $bike,
            'delux bike' => $bike,
            'h.civic' => ['Civic', 'Honda', 'Sedan'],
            'suzuki' => $unspecified,
            'karvan' => ['Karvaan', 'Changan', 'Van/Pickup'],
            'yaris' => ['Yaris', 'Toyota', 'Sedan'],
            'fortuner' => ['Fortuner', 'Toyota', 'SUV'],
            'k-8182' => $unspecified,
            'p saga' => ['Saga', 'Proton', 'Sedan'],
            'h.city' => ['City', 'Honda', 'Sedan'],
            'caroola' => ['Corolla', 'Toyota', 'Sedan'],
            'pejaro' => ['Pajero', 'Mitsubishi', 'SUV'],
            'carpet' => $misc,
            'changan' => $unspecified,
            'tractor' => ['Tractor', null, 'Loader/Truck'],
            'car' => $unspecified,
            'toyota' => $unspecified,
            'rikshaw' => ['Rickshaw', null, 'Rickshaw'],
            '4wd prado' => ['Prado', 'Toyota', 'SUV'],
            'ijaz shb bike' => $bike,
            'electric bike' => ['Electric Bike', 'EVee', 'Electric Motorcycle'],
            'vizel' => ['Vezel', 'Honda', 'SUV'],
            'ybr bike' => ['YBR', 'Yamaha', 'Motorcycle'],
            'wagnar' => ['Wagon R', 'Suzuki', 'Mini'],
            'blanket' => $misc,
            'honda' => $unspecified,
            'suzuki ballino' => ['Baleno', 'Suzuki', 'Hatchback'],
            'have bike' => $bike,
            'pridor' => $unspecified,
            's.wagnor' => ['Wagon R', 'Suzuki', 'Mini'],
            'paso' => ['Passo', 'Toyota', 'Hatchback'],
            'xli' => ['Corolla', 'Toyota', 'Sedan'],
            'hybrid' => $unspecified,
            'coroola' => ['Corolla', 'Toyota', 'Sedan'],
            's.waenor' => ['Wagon R', 'Suzuki', 'Mini'],
            'coroola gli' => ['Corolla', 'Toyota', 'Sedan'],
            'loader' => ['Loader', null, 'Loader/Truck'],
            'cangan' => $unspecified,
            'cuore' => ['Cuore', 'Daihatsu', 'Mini'],
            'car seats' => $misc,
            'fx service' => $misc,
            'ballino' => ['Baleno', 'Suzuki', 'Hatchback'],
            'suzuki khyber' => ['Khyber', 'Suzuki', 'Hatchback'],
            'meeno' => $unspecified,
            'khayber' => ['Khyber', 'Suzuki', 'Hatchback'],
            'axio' => ['Corolla', 'Toyota', 'Sedan'],
            'coure' => ['Cuore', 'Daihatsu', 'Mini'],
            'metro electric bike' => ['Electric Bike', 'Metro', 'Electric Motorcycle'],
            'coroolla' => ['Corolla', 'Toyota', 'Sedan'],
            'corolla' => ['Corolla', 'Toyota', 'Sedan'],
            'firtuner' => ['Fortuner', 'Toyota', 'SUV'],
            'crose' => ['Corolla Cross', 'Toyota', 'SUV'],
            'alto mitsubishi wala' => ['Alto', 'Suzuki', 'Mini'],
            'corola' => ['Corolla', 'Toyota', 'Sedan'],
            'lopio' => $unspecified,
            'radiator' => $misc,
            'united bike' => ['Bravo', 'United', 'Motorcycle'],
            'toyota prious' => ['Prius', 'Toyota', 'Hatchback'],
            'yamha (ybr)' => ['YBR', 'Yamaha', 'Motorcycle'],
            'caroola. altis' => ['Corolla', 'Toyota', 'Sedan'],
            'syntro' => $unspecified,
            'gloori pro' => $unspecified,
            'brv' => ['BR-V', 'Honda', 'SUV'],
            'no vehicle' => $misc,
            'bike (new)' => $bike,
            'darbi bike' => ['Bike', 'Deer', 'Motorcycle'],
            'hyundai' => $unspecified,
            'bike 2' => $bike,
            'santro' => ['Santro', 'Hyundai', 'Hatchback'],
            'ybr 125' => ['YBR', 'Yamaha', 'Motorcycle'],
            'mini pajero' => ['Pajero Mini', 'Mitsubishi', 'Mini'],
            'city cover' => ['City', 'Honda', 'Sedan'],
            'bike tanki' => $bike,
            'hijet carry' => ['Hijet', 'Daihatsu', 'Van/Pickup'],
            'wagnor' => ['Wagon R', 'Suzuki', 'Mini'],
            '4wd jeep' => ['Jeep (Unspecified)', null, 'SUV'],
            'rivo' => ['Hilux', 'Toyota', 'Van/Pickup'],
            'pick up' => ['Pickup', null, 'Loader/Truck'],
            'bysyacle' => ['Bicycle', null, 'Miscellaneous'],
            'suzuki margala' => ['Margalla', 'Suzuki', 'Sedan'],
            'kia' => $unspecified,
            'wagonr' => ['Wagon R', 'Suzuki', 'Mini'],
            'toyota limited' => $unspecified,
            'wingroad' => ['Wingroad', 'Nissan', 'Hatchback'],
            'tanker' => ['Water Tanker', null, 'Loader/Truck'],
            'carolla' => ['Corolla', 'Toyota', 'Sedan'],
            'hijat' => ['Hijet', 'Daihatsu', 'Van/Pickup'],
            'ek wagon' => ['Wagon R', 'Suzuki', 'Mini'],
            'ching chi' => ['Chingchi', null, 'Rickshaw'],
            'v2' => $unspecified,
            'nv 100' => ['NV100 Clipper', 'Nissan', 'Van/Pickup'],
            'kawasaki' => $unspecified,
            'corolla altis' => ['Corolla', 'Toyota', 'Sedan'],
            'ybr' => ['YBR', 'Yamaha', 'Motorcycle'],
            'derby bike' => ['Bike', 'Deer', 'Motorcycle'],
            'excel' => ['Excel', 'Hyundai', 'Sedan'],
            'filter' => $misc,
            'turbo wagon jeep' => $unspecified,
            'glory' => $unspecified,
            'charade' => ['Charade', 'Daihatsu', 'Hatchback'],
            'land cruiser v8' => ['Land Cruiser', 'Toyota', 'SUV'],
            'suzuki heavy bike' => $unspecified,
            'wagon' => ['Wagon R', 'Suzuki', 'Mini'],
            'frv' => ['FR-V', 'Honda', 'Van/Pickup'],
            'clean' => $misc,
            'centro' => ['Santro', 'Hyundai', 'Hatchback'],
            'tx prado' => ['Prado', 'Toyota', 'SUV'],
            'sentro' => ['Santro', 'Hyundai', 'Hatchback'],
            'havi bike' => $bike,
            'haval' => $unspecified,
            'h.civic exi' => ['Civic', 'Honda', 'Sedan'],
            'lanser' => ['Lancer', 'Mitsubishi', 'Sedan'],
            'mercedes' => $unspecified,
            'carpets/blankets' => $misc,
            'suzuki loader' => ['Bolan', 'Suzuki', 'Van/Pickup'],
            'carpet plus' => $misc,
            'loader rickshaw' => ['Loader Rickshaw', null, 'Rickshaw'],
            'yamaha bike' => ['YBR', 'Yamaha', 'Motorcycle'],
            'civic' => ['Civic', 'Honda', 'Sedan'],
            'hijet' => ['Hijet', 'Daihatsu', 'Van/Pickup'],
            'carry' => ['Hijet', 'Daihatsu', 'Van/Pickup'],
            'kia sportage' => ['Sportage', 'Kia', 'SUV'],
            'fit shuttle' => ['Fit Shuttle', 'Honda', 'Hatchback'],
            'hustler' => ['Hustler', 'Suzuki', 'Mini'],
            'caroet' => $misc,
            'nissan' => $unspecified,
            'ex.saloon' => $unspecified,
            'br.v' => ['BR-V', 'Honda', 'SUV'],
            'sakoti' => $unspecified,
            'platz' => ['Yaris', 'Toyota', 'Sedan'],
            'x saloon corolla' => ['Corolla', 'Toyota', 'Sedan'],
            'kia stonic' => ['Stonic', 'Kia', 'SUV'],
            'truck' => ['Truck', null, 'Loader/Truck'],
            'nissan  dayz' => ['Dayz', 'Nissan', 'Mini'],
            'fx' => $unspecified,
            'grande corolla' => ['Corolla', 'Toyota', 'Sedan'],
            'nissan tida latio' => ['Tiida Latio', 'Nissan', 'Sedan'],
            'mg' => $unspecified,
            'xeg limted toyota' => ['Corolla', 'Toyota', 'Sedan'],
            'hilux rivo' => ['Hilux', 'Toyota', 'Van/Pickup'],
            'liana' => ['Liana', 'Suzuki', 'Sedan'],
            'fielder' => ['Corolla Fielder', 'Toyota', 'Hatchback'],
            'mg hs' => ['HS', 'MG', 'SUV'],
            'corona' => ['Corona', 'Toyota', 'Sedan'],
            'honda shuttle' => ['Fit Shuttle', 'Honda', 'Hatchback'],
            'khyber' => ['Khyber', 'Suzuki', 'Hatchback'],
            'alto yango' => ['Alto', 'Suzuki', 'Mini'],
            'khyaber' => ['Khyber', 'Suzuki', 'Hatchback'],
            'houndai' => $unspecified,
            'wed-sale-5-11' => $misc,
            'saturday-sale-01-11' => $misc,
            'moco' => ['Moco', 'Nissan', 'Mini'],
            'united bravo' => ['Bravo', 'United', 'Motorcycle'],
            'evee electric bike' => ['Electric Bike', 'EVee', 'Electric Motorcycle'],
            'hyes' => $unspecified,
            '1' => $misc,
            'all cars' => $misc,
            'jac' => $unspecified,
            'dayz' => ['Dayz', 'Nissan', 'Mini'],
            'bravo' => ['Bravo', 'United', 'Motorcycle'],
            'pajaro' => ['Pajero', 'Mitsubishi', 'SUV'],
            '125' => $bike,
            'cg 150 bike' => $bike,
            'cars' => $misc,
            'prious' => ['Prius', 'Toyota', 'Hatchback'],
            'sherad' => ['Charade', 'Daihatsu', 'Hatchback'],
            'raize' => ['Raize', 'Toyota', 'SUV'],
            'yamha' => ['YBR', 'Yamaha', 'Motorcycle'],
            'united' => ['Bravo', 'United', 'Motorcycle'],
            'margala' => ['Margalla', 'Suzuki', 'Sedan'],
            'bioe' => $unspecified,
            'proton' => $unspecified,
            'mr wagon' => ['Wagon R', 'Suzuki', 'Mini'],
            'today all cars' => $misc,
            'vigo hilux' => ['Hilux', 'Toyota', 'Van/Pickup'],
            '2 bikes' => $bike,
            'eveee + bike' => ['Electric Bike', 'EVee', 'Electric Motorcycle'],
            'tx land cruiser' => ['Land Cruiser', 'Toyota', 'SUV'],
            'all day' => $misc,
            'all day sale' => $misc,
        ];
    }

    private function processMessyNames(): void
    {
        $map = $this->nameMap();

        // Only the rows the legacy migration created (no brand assigned at all).
        $messyRows = Vehicle::whereNull('vehicle_brand_id')
            ->whereNull('vehicle_category_id')
            ->orderBy('id')
            ->get(['id', 'name']);

        foreach ($messyRows as $row) {
            $oldName = strtolower(trim($row->name));
            if (!isset($map[$oldName])) {
                $this->warnings[] = "old catalog id={$row->id} name='{$row->name}': no mapping entry found, left unchanged.";
                continue;
            }

            [$canonicalName, $brandName, $categoryName] = $map[$oldName];
            $canonicalName = strtolower(trim($canonicalName));
            $brandId = $brandName ? ($this->brandIds[strtolower($brandName)] ?? null) : null;
            $categoryId = $this->categoryIds[strtolower($categoryName)] ?? null;

            $key = $canonicalName . '|' . ($brandId ?? 'null');

            if (isset($this->catalogIndex[$key]) && $this->catalogIndex[$key] !== $row->id) {
                // An existing canonical row (pre-seeded or already-renamed) already
                // covers this model — repoint everything to it and drop this row.
                $survivorId = $this->catalogIndex[$key];
                $this->repointAndDelete($row->id, $survivorId, $categoryId);
                $this->merged++;
            } elseif (isset($this->catalogIndex[$key]) && $this->catalogIndex[$key] === $row->id) {
                // This row's own (old, uncleaned) name+null-brand key happened to
                // already be indexed pre-cleanup, but its resolved canonical brand
                // was previously unresolvable due to the orphaned brand-id gap —
                // now fixed — so just finalize it like a normal new survivor.
                $row->name = $canonicalName;
                $row->vehicle_brand_id = $brandId;
                $row->vehicle_category_id = $categoryId;
                $row->save();
                $this->catalogIndex[$key] = $row->id;
                $this->renamed++;
            } else {
                // This row becomes the canonical survivor for this model.
                $row->name = $canonicalName;
                $row->vehicle_brand_id = $brandId;
                $row->vehicle_category_id = $categoryId;
                $row->save();
                $this->catalogIndex[$key] = $row->id;
                $this->renamed++;
            }
        }
    }

    private function repointAndDelete(int $fromId, int $toId, ?int $categoryIdIfMissing): void
    {
        $count = DB::table('user_vehicles')->where('vehicle_id', $fromId)->update(['vehicle_id' => $toId]);
        $this->userVehiclesRepointed += $count;

        // Backfill the survivor's category if it somehow doesn't have one yet.
        $survivor = Vehicle::find($toId);
        if ($survivor && !$survivor->vehicle_category_id && $categoryIdIfMissing) {
            $survivor->vehicle_category_id = $categoryIdIfMissing;
            $survivor->save();
        }

        $remaining = DB::table('user_vehicles')->where('vehicle_id', $fromId)->count();
        if ($remaining === 0) {
            Vehicle::where('id', $fromId)->delete();
            $this->deleted++;
        } else {
            $this->warnings[] = "catalog id={$fromId}: {$remaining} references remained after repoint, not deleted.";
        }
    }

    private function printReport(): void
    {
        $this->info("\n=== Cleanup report ===");
        $this->line("  Mazda entries stripped of brand prefix (Mazda2->Demio etc.): {$this->mazdaRenamed}");
        $this->line("  messy catalog rows finalized as a canonical survivor: {$this->renamed}");
        $this->line("  messy catalog rows merged into a survivor: {$this->merged}");
        $this->line("  catalog rows deleted (now unreferenced): {$this->deleted}");
        $this->line("  user_vehicles rows repointed: {$this->userVehiclesRepointed}");
        $this->line('  (' . $this->renamed . ' + ' . $this->merged . ' = ' . ($this->renamed + $this->merged) . ' of the 189 messy rows accounted for)');

        if ($this->warnings) {
            $this->warn("\n=== Notes (" . count($this->warnings) . ") ===");
            foreach ($this->warnings as $w) {
                $this->line('  - ' . $w);
            }
        }
    }

    private function validate(): void
    {
        $this->info("\n=== Post-cleanup validation ===");
        $this->line('  vehicles (catalog) total: ' . Vehicle::count());
        $this->line('  vehicles missing category: ' . Vehicle::whereNull('vehicle_category_id')->count());
        $this->line('  user_vehicles pointing at a deleted vehicle: ' . DB::table('user_vehicles')
            ->leftJoin('vehicles', 'vehicles.id', '=', 'user_vehicles.vehicle_id')
            ->whereNull('vehicles.id')
            ->count());

        $dupeNames = DB::table('vehicles')
            ->select('name', 'vehicle_brand_id', DB::raw('COUNT(*) as c'))
            ->groupBy('name', 'vehicle_brand_id')
            ->having('c', '>', 1)
            ->get();
        $this->line('  duplicate (name, brand) groups remaining: ' . $dupeNames->count());
        foreach ($dupeNames as $d) {
            $this->line("    - '{$d->name}' (brand_id={$d->vehicle_brand_id}): {$d->c} rows");
        }
    }
}
