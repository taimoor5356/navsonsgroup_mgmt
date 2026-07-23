<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * General-purpose, reusable vehicle-catalog deduplication tool.
 *
 * Scans the `vehicles` table (this app's shared vehicle-model catalog),
 * groups records that represent the same real-world model using a curated
 * synonym dictionary plus generic brand/noise-token normalization, previews
 * every proposed merge, asks for confirmation, then — inside a single
 * transaction — repoints every column in the database that references
 * vehicles.id (auto-discovered via information_schema, never hardcoded) from
 * each duplicate's id to the chosen master id, and force-deletes the
 * duplicate (the model uses SoftDeletes, so a plain delete() would only set
 * deleted_at and leave the row physically present).
 *
 * Usage:
 *   php artisan vehicles:normalize              # preview + confirm + apply
 *   php artisan vehicles:normalize --dry-run     # preview only, never writes
 *   php artisan vehicles:normalize --force       # apply without confirmation
 */
class VehiclesNormalize extends Command
{
    protected $signature = 'vehicles:normalize
        {--dry-run : Preview proposed merges only; never writes to the database}
        {--force : Apply without an interactive confirmation prompt}';

    protected $description = 'Find and merge duplicate/equivalent vehicle catalog records, repointing every reference first';

    /** @var array<string,int> normalized brand name => id (for prefix/suffix stripping) */
    private array $brandNames = [];

    /** @var array<int,array{table:string,column:string}> auto-discovered FK-style references to vehicles.id */
    private array $referencingColumns = [];

    private array $skipped = [];

    public function handle(): int
    {
        $start = microtime(true);
        $dryRun = (bool) $this->option('dry-run');

        $this->discoverReferencingColumns();
        $this->loadBrandNames();

        $vehicles = Vehicle::orderBy('id')->get(['id', 'name', 'vehicle_brand_id', 'vehicle_category_id']);
        $this->info("Scanned {$vehicles->count()} vehicle catalog records.");

        $groups = $this->buildGroups($vehicles);
        $mergeGroups = array_filter($groups, fn ($g) => count($g['members']) > 1);

        if (empty($mergeGroups)) {
            $this->info('No duplicate groups found. Nothing to do.');
            $this->printSkipped();
            return 0;
        }

        $this->previewMerges($mergeGroups);

        if ($dryRun) {
            $this->info("\nDry run only — no changes written.");
            $this->printSkipped();
            return 0;
        }

        if (!$this->option('force') && !$this->confirm(
            "\nProceed with merging " . count($mergeGroups) . ' group(s) covering ' .
            array_sum(array_map(fn ($g) => count($g['members']), $mergeGroups)) . ' records?'
        )) {
            $this->warn('Aborted — no changes made.');
            return 1;
        }

        $recordsMerged = 0;
        $fkUpdates = 0;
        $rowsDeleted = 0;

        $bar = $this->output->createProgressBar(count($mergeGroups));
        $bar->start();

        DB::beginTransaction();
        try {
            foreach ($mergeGroups as $group) {
                $master = $group['master'];
                foreach ($group['members'] as $member) {
                    if ($member->id === $master->id) {
                        continue;
                    }
                    foreach ($this->referencingColumns as $ref) {
                        $updated = DB::table($ref['table'])
                            ->where($ref['column'], $member->id)
                            ->update([$ref['column'] => $master->id]);
                        $fkUpdates += $updated;
                    }

                    $remaining = 0;
                    foreach ($this->referencingColumns as $ref) {
                        $remaining += DB::table($ref['table'])->where($ref['column'], $member->id)->count();
                    }
                    if ($remaining > 0) {
                        $this->skipped[] = "id={$member->id} name='{$member->name}': {$remaining} reference(s) remained after update, left in place (not deleted) — needs manual review.";
                        continue;
                    }

                    Log::info('vehicles:normalize merged record', [
                        'duplicate_id' => $member->id,
                        'duplicate_name' => $member->name,
                        'master_id' => $master->id,
                        'master_name' => $master->name,
                    ]);

                    Vehicle::withTrashed()->where('id', $member->id)->forceDelete();
                    $rowsDeleted++;
                    $recordsMerged++;
                }

                // Canonicalize the master's own name/brand/category if the group's
                // synonym rule specified one and the master doesn't already have it.
                if ($group['canonicalName'] && strtolower(trim($master->name)) !== $group['canonicalName']) {
                    $master->name = $group['canonicalName'];
                }
                if ($group['brandId'] && !$master->vehicle_brand_id) {
                    $master->vehicle_brand_id = $group['brandId'];
                }
                if ($group['categoryId'] && !$master->vehicle_category_id) {
                    $master->vehicle_category_id = $group['categoryId'];
                }
                $master->save();

                $bar->advance();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $bar->finish();
            $this->newLine(2);
            $this->error('Normalization failed and was rolled back: ' . $e->getMessage());
            return 1;
        }

        $bar->finish();
        $this->newLine(2);

        $elapsed = round(microtime(true) - $start, 2);

        $this->info('=== Final report ===');
        $this->table(['Metric', 'Value'], [
            ['Total vehicle models scanned', $vehicles->count()],
            ['Duplicate groups found', count($mergeGroups)],
            ['Records merged', $recordsMerged],
            ['Foreign key updates performed', $fkUpdates],
            ['Duplicate rows deleted', $rowsDeleted],
            ['Time taken', "{$elapsed}s"],
            ['Skipped / ambiguous records', count($this->skipped)],
        ]);

        $this->printSkipped();

        return 0;
    }

    /**
     * Find every table + column in this database that references vehicles.id,
     * by column-name convention (this app doesn't use real FK constraints
     * anywhere — confirmed during earlier migration work — so we match on the
     * literal column name rather than information_schema.KEY_COLUMN_USAGE).
     */
    private function discoverReferencingColumns(): void
    {
        $rows = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('COLUMN_NAME', 'vehicle_id')
            ->where('TABLE_NAME', '!=', 'vehicles')
            ->get(['TABLE_NAME', 'COLUMN_NAME']);

        foreach ($rows as $row) {
            $this->referencingColumns[] = ['table' => $row->TABLE_NAME, 'column' => $row->COLUMN_NAME];
        }

        if (empty($this->referencingColumns)) {
            $this->warn('No referencing columns discovered — is this the right database?');
        } else {
            $this->line('Discovered referencing columns: ' . collect($this->referencingColumns)
                ->map(fn ($r) => "{$r['table']}.{$r['column']}")->implode(', '));
        }
    }

    private function loadBrandNames(): void
    {
        foreach (DB::table('vehicle_brands')->get(['id', 'name']) as $b) {
            $this->brandNames[$b->id] = strtolower(trim($b->name));
        }
    }

    /**
     * Explicit token-normalization groups for well-known ambiguous cases
     * (matched by exact normalized-name after generic cleanup).
     */
    private function explicitGroups(): array
    {
        return [
            'jeep' => ['name' => 'Jeep', 'brand' => null, 'category' => null],
            'prado' => ['name' => 'Prado', 'brand' => 'toyota', 'category' => null],
            'land cruiser v8' => ['name' => 'Land Cruiser V8', 'brand' => 'toyota', 'category' => null],
        ];
    }

    /**
     * Brand names that are also common generic/English words and must NOT be
     * stripped by the generic normalizer: "Jeep" is used constantly by
     * customers as a generic vehicle-type word (not the brand), and "MINI" as
     * a size descriptor ("Pajero Mini") — stripping either causes false merges
     * (confirmed via dry-run: "Pajero Mini" collapsing into "Pajero", and a
     * "Jeep (Unspecified)" catalog row collapsing into an unrelated
     * "Unspecified" junk-bucket row).
     */
    private const AMBIGUOUS_BRAND_WORDS = ['jeep', 'mini'];

    private function normalize(string $name): string
    {
        $n = strtolower(trim($name));
        $n = preg_replace('/[^a-z0-9\s]/', ' ', $n);

        // Strip any known brand name appearing anywhere in the string (prefix or
        // suffix) — except ones that double as common generic words.
        foreach ($this->brandNames as $brand) {
            if (in_array($brand, self::AMBIGUOUS_BRAND_WORDS, true)) {
                continue;
            }
            $n = preg_replace('/\b' . preg_quote($brand, '/') . '\b/', ' ', $n);
        }

        // Strip common trim/spec/noise tokens that don't change the underlying model.
        $noise = [
            '4wd', '4x4', 'awd', 'fwd', 'rwd', 'turbo', 'efi', 'petrol', 'diesel',
            'hybrid', 'electric', 'gli', 'xli', 'vxl', 'grande', 'reborn', 'rebirth',
            'limited', 'ltd', 'std', 'automatic', 'manual', 'new', 'old',
        ];
        foreach ($noise as $word) {
            $n = preg_replace('/\b' . preg_quote($word, '/') . '\b/', ' ', $n);
        }

        // Strip cc values (125cc, 70 cc) and bare engine-size numbers used alone.
        $n = preg_replace('/\b\d+\s*cc\b/', ' ', $n);

        $n = preg_replace('/\s+/', ' ', $n);
        return trim($n);
    }

    private function buildGroups($vehicles): array
    {
        $explicit = $this->explicitGroups();
        $groups = [];

        foreach ($vehicles as $v) {
            // Checked on the RAW name, before "electric" is stripped as generic
            // noise below — an electric bike must never collapse into the same
            // bucket as a regular one (separate brand/category by design, see
            // the earlier vehicle-catalog cleanup).
            $isElectric = (bool) preg_match('/\belectric\b/i', $v->name);

            $normalized = $this->normalize($v->name);

            if ($normalized === '') {
                $this->skipped[] = "id={$v->id} name='{$v->name}': normalized to an empty string, left unchanged — needs manual review.";
                continue;
            }

            // "bike"-style entries: a bare/near-bare number (was "125", "70", "150",
            // "125 bike", etc.) or the literal word "bike" becomes the generic
            // "Bike"/"Electric Bike" bucket — but ONLY when the record has no real
            // brand assigned. A BRANDED bike (Honda CD 70, a Deer/Yamaha/United
            // entry from the earlier catalog cleanup) or a branded car that
            // happens to be a bare number (Porsche 911, Peugeot 208, Ram 1500, MG
            // 3...) is a real, distinct, already-identified vehicle and must never
            // be swept into this generic bucket.
            $isBareBikeCandidate = $v->vehicle_brand_id === null
                && (preg_match('/^\d*$/', $normalized) || $normalized === 'bike' || $isElectric);

            if ($isBareBikeCandidate) {
                $normalized = $isElectric ? 'electric bike' : 'bike';
            }

            $canonicalName = null;
            $brandId = null;
            $categoryId = null;
            $crossBrandAllowed = false;

            if (isset($explicit[$normalized])) {
                $rule = $explicit[$normalized];
                $canonicalName = strtolower($rule['name']);
                if ($rule['brand']) {
                    $brandId = array_search($rule['brand'], $this->brandNames) ?: null;
                }
                if ($rule['category']) {
                    $categoryId = DB::table('vehicle_categories')->where('name', $rule['category'])->value('id');
                }
                $crossBrandAllowed = true;
            } elseif ($isBareBikeCandidate && $normalized === 'bike') {
                $canonicalName = 'bike';
                $categoryId = DB::table('vehicle_categories')->where('name', 'Motorcycle')->value('id');
                $crossBrandAllowed = true;
            } elseif ($isBareBikeCandidate && $normalized === 'electric bike') {
                $canonicalName = 'electric bike';
                $categoryId = DB::table('vehicle_categories')->where('name', 'Electric Motorcycle')->value('id');
                $crossBrandAllowed = true;
            }

            // Explicitly-curated groups (and the bike bucket) are deliberately
            // allowed to merge across brand/null-brand boundaries — that's the
            // whole point of curating them. Everything else discovered by the
            // generic normalizer only merges within the SAME brand (or both
            // null), so two different real cars that just happen to share a
            // generic model name under different manufacturers (e.g. a Tesla
            // "Roadster" vs a MINI "Roadster") never collide.
            $key = $crossBrandAllowed ? $normalized : ($normalized . '|' . ($v->vehicle_brand_id ?? 'null'));

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'members' => [],
                    'canonicalName' => $canonicalName,
                    'brandId' => $brandId,
                    'categoryId' => $categoryId,
                ];
            }
            $groups[$key]['members'][] = $v;
        }

        foreach ($groups as $key => &$group) {
            $group['master'] = $this->pickMaster($group['members']);
        }
        unset($group);

        return $groups;
    }

    /**
     * Prefer the most "complete" record (has brand + category already), then
     * the shortest/cleanest name, then the lowest id (oldest / most likely to
     * already be referenced elsewhere and thus cheaper to keep as master).
     */
    private function pickMaster($members)
    {
        $scored = collect($members)->map(function ($v) {
            $score = 0;
            if ($v->vehicle_brand_id) $score += 2;
            if ($v->vehicle_category_id) $score += 2;
            return [$v, $score];
        })->sortByDesc(fn ($pair) => $pair[1])
          ->sortBy(fn ($pair) => $pair[0]->id)
          ->sortByDesc(fn ($pair) => $pair[1]);

        return $scored->first()[0];
    }

    private function previewMerges(array $mergeGroups): void
    {
        $this->info("\n=== Proposed merges (" . count($mergeGroups) . ' groups) ===');
        $rows = [];
        foreach ($mergeGroups as $group) {
            $master = $group['master'];
            $others = collect($group['members'])->reject(fn ($m) => $m->id === $master->id)
                ->map(fn ($m) => "{$m->name} (#{$m->id})")->implode(', ');
            $rows[] = [
                $group['canonicalName'] ?: $master->name,
                "{$master->name} (#{$master->id})",
                $others,
                count($group['members']),
            ];
        }
        $this->table(['Canonical name', 'Master record kept', 'Duplicates to merge in', 'Group size'], $rows);
    }

    private function printSkipped(): void
    {
        if (!$this->skipped) {
            return;
        }
        $this->warn("\n=== Skipped / ambiguous (" . count($this->skipped) . ') — needs manual review ===');
        foreach ($this->skipped as $s) {
            $this->line('  - ' . $s);
        }
    }
}
