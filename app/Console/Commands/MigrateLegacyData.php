<?php

namespace App\Console\Commands;

use App\Models\ExpenseType;
use App\Models\PaymentMode;
use App\Models\ServiceType;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * One-off migration of data from the pre-refactor database (imported into the
 * `mysql_old` connection from database/old_db.sql) into the current schema.
 *
 * See database/MIGRATION_MAPPING.md for the full analysis and reasoning behind
 * every mapping decision here. Key points:
 * - old `vehicles` (one row per customer's specific car) splits into new
 *   `vehicles` (shared model catalog) + `user_vehicles` (per-registration row).
 * - reference tables (roles, payment_modes, service_types, expense_types) are
 *   reconciled by NAME, never by id, since the live "new" database already has
 *   its own independently-seeded rows for these.
 * - every entity gets an explicit old-id -> new-id map built during the run;
 *   nothing assumes id equality between old and new.
 */
class MigrateLegacyData extends Command
{
    protected $signature = 'legacy:migrate {--dry-run : Run the full migration inside a transaction and roll it back at the end}';

    protected $description = 'Migrate data from the legacy pre-refactor database (mysql_old) into the current schema';

    private $old;

    private array $roleMap = [];
    private array $paymentModeMap = [];
    private array $serviceTypeMap = [];
    private array $expenseTypeMap = [];
    private array $userMap = [];
    private array $vehicleCatalogMap = []; // normalized name => new vehicles.id
    private array $userVehicleMap = [];    // old vehicles.id => new user_vehicles.id
    private array $report = [];
    private array $warnings = [];

    public function handle(): int
    {
        $this->old = DB::connection('mysql_old');
        $dryRun = (bool) $this->option('dry-run');

        if (!$this->old->getSchemaBuilder()->hasTable('users')) {
            $this->error('mysql_old connection has no `users` table — is the legacy database imported and configured?');
            return 1;
        }

        $this->info($dryRun ? 'DRY RUN — will roll back at the end, no data will persist.' : 'Starting legacy data migration...');

        DB::beginTransaction();

        try {
            $this->migrateRoles();
            $this->migratePaymentModes();
            $this->migrateServiceTypes();
            $this->migrateExpenseTypes();
            $this->migrateUsers();
            $this->migrateModelHasRoles();
            $this->migrateVehiclesAndUserVehicles();
            $this->migrateServices();
            $this->migrateExpenses();
            $this->migrateFines();

            if ($dryRun) {
                DB::rollBack();
                $this->info("\nDry run finished — transaction rolled back, nothing was persisted.");
            } else {
                DB::commit();
                $this->info("\nMigration committed.");
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Migration failed and was rolled back: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }

        $this->printReport();

        if (!$dryRun) {
            $this->validate();
        }

        return 0;
    }

    private function migrateRoles(): void
    {
        foreach ($this->old->table('roles')->get() as $old) {
            $new = Role::firstOrCreate(
                ['name' => $old->name, 'guard_name' => $old->guard_name ?: 'web']
            );
            $this->roleMap[$old->id] = $new->id;
        }
        $this->report[] = 'roles: ' . count($this->roleMap) . ' reconciled by name -> ' . json_encode($this->roleMap);
    }

    private function migratePaymentModes(): void
    {
        foreach ($this->old->table('payment_modes')->get() as $old) {
            $new = PaymentMode::firstOrCreate(['name' => $old->name]);
            $this->paymentModeMap[$old->id] = $new->id;
        }
        $this->report[] = 'payment_modes: ' . count($this->paymentModeMap) . ' reconciled by name -> ' . json_encode($this->paymentModeMap);
    }

    private function migrateServiceTypes(): void
    {
        foreach ($this->old->table('service_types')->get() as $old) {
            $new = ServiceType::firstOrCreate(['name' => $old->name]);
            $this->serviceTypeMap[$old->id] = $new->id;
        }
        $this->report[] = 'service_types: ' . count($this->serviceTypeMap) . ' reconciled by name -> ' . json_encode($this->serviceTypeMap);
    }

    private function migrateExpenseTypes(): void
    {
        foreach ($this->old->table('expense_types')->get() as $old) {
            $new = ExpenseType::firstOrCreate(['name' => $old->name]);
            $this->expenseTypeMap[$old->id] = $new->id;
        }
        $this->report[] = 'expense_types: ' . count($this->expenseTypeMap) . ' reconciled by name -> ' . json_encode($this->expenseTypeMap);
    }

    private function migrateUsers(): void
    {
        $count = 0;
        $emailCollisions = 0;

        $this->old->table('users')->orderBy('id')->chunk(200, function ($rows) use (&$count, &$emailCollisions) {
            foreach ($rows as $old) {
                $email = $old->email;
                if (DB::table('users')->where('email', $email)->exists()) {
                    $emailCollisions++;
                    $email = 'legacy_' . $old->id . '_' . $email;
                }

                $newId = DB::table('users')->insertGetId([
                    'name' => strtolower(trim($old->name)),
                    'email' => $email,
                    'phone' => $old->phone,
                    'address' => $old->address,
                    // user_type is a FK into roles by id — must be remapped like any
                    // other foreign key, not copied raw (old id-space != new id-space).
                    'user_type' => $this->roleMap[$old->user_type] ?? null,
                    'is_active' => $old->is_active,
                    'email_verified_at' => $old->email_verified_at,
                    'password' => $old->password, // already a valid bcrypt hash — do not rehash
                    'remember_token' => $old->remember_token,
                    'user_address_id' => null,
                    'user_pic' => null,
                    'cnic_pic' => null,
                    'cnic_number' => null,
                    'deleted_at' => $old->deleted_at,
                    'created_at' => $old->created_at,
                    'updated_at' => $old->updated_at,
                ]);

                $this->userMap[$old->id] = $newId;
                $count++;
            }
        });

        $this->report[] = "users: {$count} migrated" . ($emailCollisions ? ", {$emailCollisions} email collisions resolved with a 'legacy_<old_id>_' prefix" : ', no email collisions');
    }

    private function migrateModelHasRoles(): void
    {
        $count = 0;
        $skipped = 0;

        $this->old->table('model_has_roles')->orderBy('model_id')->chunk(300, function ($rows) use (&$count, &$skipped) {
            $inserts = [];
            foreach ($rows as $old) {
                $newUserId = $this->userMap[$old->model_id] ?? null;
                $newRoleId = $this->roleMap[$old->role_id] ?? null;
                if (!$newUserId || !$newRoleId) {
                    $skipped++;
                    continue;
                }
                $inserts[] = [
                    'role_id' => $newRoleId,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $newUserId,
                ];
            }
            if ($inserts) {
                DB::table('model_has_roles')->insertOrIgnore($inserts);
                $count += count($inserts);
            }
        });

        $this->report[] = "model_has_roles: {$count} migrated" . ($skipped ? ", {$skipped} skipped (missing user/role mapping)" : '');
    }

    private function migrateVehiclesAndUserVehicles(): void
    {
        // Preload the existing catalog (582 real model rows already seeded) so old
        // names get matched against it case-insensitively instead of creating dupes.
        foreach (Vehicle::all(['id', 'name']) as $v) {
            $this->vehicleCatalogMap[strtolower(trim($v->name))] = $v->id;
        }

        $catalogCreated = 0;
        $userVehiclesCreated = 0;
        $skippedNoUser = 0;
        $registrationSuffixed = 0;
        $seenRegistrations = [];

        // Pre-seed with what's already live so we detect collisions against existing data too.
        foreach (DB::table('user_vehicles')->pluck('registration_number') as $reg) {
            $seenRegistrations[strtolower($reg)] = true;
        }

        $this->old->table('vehicles')->orderBy('id')->chunk(200, function ($rows) use (
            &$catalogCreated, &$userVehiclesCreated, &$skippedNoUser, &$registrationSuffixed, &$seenRegistrations
        ) {
            foreach ($rows as $old) {
                $newUserId = $this->userMap[$old->user_id] ?? null;
                if (!$newUserId) {
                    $skippedNoUser++;
                    $this->warnings[] = "old vehicle id={$old->id} (reg={$old->registration_number}): no matching migrated user for old user_id={$old->user_id}, skipped.";
                    continue;
                }

                $normalizedName = strtolower(trim((string) $old->name)) ?: 'unknown';
                if (!isset($this->vehicleCatalogMap[$normalizedName])) {
                    $catalogId = DB::table('vehicles')->insertGetId([
                        'name' => $normalizedName,
                        'vehicle_brand_id' => null,
                        'vehicle_category_id' => null,
                        'service_count' => 0,
                        'deleted_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->vehicleCatalogMap[$normalizedName] = $catalogId;
                    $catalogCreated++;
                }
                $catalogId = $this->vehicleCatalogMap[$normalizedName];

                $regNumber = strtolower(trim((string) $old->registration_number));
                $finalReg = $regNumber;
                if (isset($seenRegistrations[$regNumber])) {
                    $suffix = 2;
                    while (isset($seenRegistrations[$regNumber . '-dup' . $suffix])) {
                        $suffix++;
                    }
                    $finalReg = $regNumber . '-dup' . $suffix;
                    $registrationSuffixed++;
                    $this->warnings[] = "old vehicle id={$old->id}: registration_number '{$old->registration_number}' collided with an already-migrated row (case-insensitive duplicate in old data) — stored as '{$finalReg}' instead.";
                }
                $seenRegistrations[$finalReg] = true;

                $newUserVehicleId = DB::table('user_vehicles')->insertGetId([
                    'user_id' => $newUserId,
                    'vehicle_id' => $catalogId,
                    'registration_number' => $finalReg,
                    'color' => $old->color,
                    'model_year' => $old->model_year,
                    'user_pic' => null,
                    'cnic_pic' => null,
                    'cnic_number' => null,
                    'vehicle_pic' => null,
                    'service_count' => $old->service_count ?? 0,
                    'deleted_at' => $old->deleted_at,
                    'created_at' => $old->created_at,
                    'updated_at' => $old->updated_at,
                ]);

                $this->userVehicleMap[$old->id] = $newUserVehicleId;
                $userVehiclesCreated++;
            }
        });

        $this->report[] = "vehicles (catalog): {$catalogCreated} new catalog rows created (matched the rest against the existing 582-row catalog by name)";
        $this->report[] = "user_vehicles: {$userVehiclesCreated} migrated, {$skippedNoUser} skipped (no matching user), {$registrationSuffixed} registration numbers suffixed to resolve a duplicate";
    }

    private function migrateServices(): void
    {
        $count = 0;
        $skipped = 0;

        $this->old->table('services')->orderBy('id')->chunk(300, function ($rows) use (&$count, &$skipped) {
            $inserts = [];
            foreach ($rows as $old) {
                $userVehicleId = $this->userVehicleMap[$old->vehicle_id] ?? null;
                if (!$userVehicleId) {
                    $skipped++;
                    $this->warnings[] = "old service id={$old->id}: no matching user_vehicle for old vehicle_id={$old->vehicle_id}, skipped.";
                    continue;
                }

                $inserts[] = [
                    'user_vehicle_id' => $userVehicleId,
                    'service_type_id' => $this->serviceTypeMap[$old->service_type_id] ?? null,
                    'diesel' => $old->diesel,
                    'polish' => $old->polish,
                    'charges' => $old->charges,
                    'discount' => $old->discount,
                    'discount_type' => 'amount',
                    'discount_value' => $old->discount,
                    'discount_reason' => $old->discount_reason ?: null,
                    'collected_amount' => $old->collected_amount,
                    'payment_mode_id' => $this->paymentModeMap[$old->payment_mode_id] ?? null,
                    'payment_status' => $old->payment_status,
                    'complain' => $old->complain,
                    'luster' => $old->luster,
                    'vaccum' => $old->vaccum,
                    'overtime' => 0,
                    'overtime_amount' => 0,
                    'deleted_at' => $old->deleted_at,
                    'created_at' => $old->created_at,
                    'updated_at' => $old->updated_at,
                ];
            }
            if ($inserts) {
                DB::table('services')->insert($inserts);
                $count += count($inserts);
            }
        });

        $this->report[] = "services: {$count} migrated" . ($skipped ? ", {$skipped} skipped (missing vehicle mapping)" : '');
    }

    private function migrateExpenses(): void
    {
        $count = 0;
        $skipped = 0;

        $this->old->table('expenses')->orderBy('id')->chunk(300, function ($rows) use (&$count, &$skipped) {
            $inserts = [];
            foreach ($rows as $old) {
                $newUserId = $old->user_id ? ($this->userMap[$old->user_id] ?? null) : null;
                if ($old->user_id && !$newUserId) {
                    $skipped++;
                    $this->warnings[] = "old expense id={$old->id}: no matching user for old user_id={$old->user_id}, migrated with user_id=NULL.";
                }

                $inserts[] = [
                    'name' => $old->name,
                    'expense_type_id' => $this->expenseTypeMap[$old->expense_type_id] ?? null,
                    'description' => $old->description,
                    'amount' => $old->amount,
                    'payment_mode_id' => $this->paymentModeMap[$old->payment_mode_id] ?? null,
                    'user_id' => $newUserId,
                    'created_at' => $old->created_at,
                    'updated_at' => $old->updated_at,
                ];
            }
            if ($inserts) {
                DB::table('expenses')->insert($inserts);
                $count += count($inserts);
            }
        });

        $this->report[] = "expenses: {$count} migrated" . ($skipped ? ", {$skipped} had an unresolvable user_id (kept, set to NULL)" : '');
    }

    private function migrateFines(): void
    {
        $count = 0;
        $skipped = 0;

        foreach ($this->old->table('fines')->orderBy('id')->get() as $old) {
            $newUserId = $this->userMap[$old->user_id] ?? null;
            $newUserVehicleId = $this->userVehicleMap[$old->vehicle_id] ?? null;
            if (!$newUserId || !$newUserVehicleId) {
                $skipped++;
                $this->warnings[] = "old fine id={$old->id}: missing user or vehicle mapping, skipped.";
                continue;
            }

            DB::table('fines')->insert([
                'user_id' => $newUserId,
                'vehicle_id' => null, // old catalog-vehicle FK has no correct new equivalent — see user_vehicle_id instead
                'user_vehicle_id' => $newUserVehicleId,
                'amount' => $old->amount,
                'reason' => $old->reason,
                'deleted_at' => $old->deleted_at,
                'created_at' => $old->created_at,
                'updated_at' => $old->updated_at,
            ]);
            $count++;
        }

        $this->report[] = "fines: {$count} migrated" . ($skipped ? ", {$skipped} skipped (missing user/vehicle mapping)" : '');
    }

    private function printReport(): void
    {
        $this->info("\n=== Migration report ===");
        foreach ($this->report as $line) {
            $this->line('  ' . $line);
        }
        if ($this->warnings) {
            $this->warn("\n=== Warnings (" . count($this->warnings) . ") ===");
            foreach ($this->warnings as $w) {
                $this->line('  - ' . $w);
            }
        }
    }

    private function validate(): void
    {
        $this->info("\n=== Post-migration validation ===");

        $checks = [
            'users' => DB::table('users')->count(),
            'user_vehicles' => DB::table('user_vehicles')->count(),
            'vehicles (catalog)' => DB::table('vehicles')->count(),
            'services' => DB::table('services')->count(),
            'expenses' => DB::table('expenses')->count(),
            'fines' => DB::table('fines')->count(),
        ];
        foreach ($checks as $label => $c) {
            $this->line("  {$label}: {$c} total rows now");
        }

        $orphanServices = DB::table('services')->whereNull('user_vehicle_id')->count();
        $orphanUserVehicles = DB::table('user_vehicles')->whereNull('user_id')->orWhereNull('vehicle_id')->count();
        $orphanFines = DB::table('fines')->whereNull('user_vehicle_id')->count();
        $servicesBadType = DB::table('services')->whereNull('service_type_id')->count();
        $servicesBadPaymentMode = DB::table('services')->whereNull('payment_mode_id')->count();

        $this->line("  orphan services (null user_vehicle_id): {$orphanServices}");
        $this->line("  orphan user_vehicles (null user_id or vehicle_id): {$orphanUserVehicles}");
        $this->line("  orphan fines (null user_vehicle_id): {$orphanFines}");
        $this->line("  services with unmapped service_type_id: {$servicesBadType}");
        $this->line("  services with unmapped payment_mode_id: {$servicesBadPaymentMode}");

        $dupRegs = DB::table('user_vehicles')
            ->select('registration_number', DB::raw('COUNT(*) as c'))
            ->groupBy('registration_number')
            ->having('c', '>', 1)
            ->count();
        $this->line("  duplicate registration_number groups remaining: {$dupRegs}");
    }
}
