# Old → New Data Migration Mapping

## Context

`database/old_db.sql` is not an unrelated legacy system — it's a snapshot of **this same
application** from before this project's schema was refactored (introduction of
`user_vehicles` as a join table, `vehicle_brands`, `vehicle_categories`,
`service_category_rates`, discount type/value, CNIC/photo fields, etc.). Confirmed by:
identical table names, identical role names/ids, identical payment mode names, and the old
`vehicles` table being structurally identical to the pre-refactor `vehicles` table earlier
this session.

**Important finding**: the live "new" database is *not* empty scaffolding. It already
contains a deliberately-seeded 582-row vehicle catalog (48 real brands × real models —
Camry, Prius, RAV4, Land Cruiser, etc. — all with `vehicle_category_id = NULL`) and
re-seeded admin/manager/user accounts, none of which came from `old_db.sql` or from work
this session. This means **no table can be safely wiped and reloaded** — every entity needs
real old-id → new-id mapping tables built during migration, exactly as your instructions
require, rather than assuming IDs carry over.

## Row counts (old_db.sql)

| Table | Rows | Notes |
|---|---|---|
| users | 1,945 | 0 soft-deleted |
| vehicles (old) | 1,936 | = one row per customer's specific car, **not** a shared model catalog |
| services | 3,344 | |
| expenses | 696 | |
| fines | 4 | |
| model_has_roles | 1,942 | Spatie role assignments |
| expense_types | 6 | |
| service_types | 6 | |
| payment_modes | 4 | |
| roles | 5 | |
| permissions / permission_modules / role_has_permissions / model_has_permissions | 0 | never used in old system |

## The core entity-split (most important mapping)

Old `vehicles` conflated three concepts that the new schema deliberately separates:

- **Old `vehicles` row** = one specific customer's one specific car (has `user_id`,
  `registration_number`, `color`, `model_year` — e.g. id=2, name="mehran", user_id=4,
  registration_number="fsp-9939").
- **New `vehicles`** = shared model catalog only (name + brand + category, no owner, no
  registration number). Already has 582 rows seeded.
- **New `user_vehicles`** = the per-registration join row (user_id, vehicle_id →catalog,
  registration_number, color, model_year, + new photo/CNIC fields).

So **one old `vehicles` row becomes one new `user_vehicles` row**, and its free-text `name`
must resolve to a **new `vehicles` catalog row** (find-or-create).

Old vehicle names are messy free text (210 distinct values after lowercasing). Top values:
`bike` (668), `mehran` (203), `alto` (165), `corolla` (129), `cultus` (84), `h.city` (68),
`h.civic` (38)... down to one-offs like `carpet`, `all day sale`, `rikshaw`, `loader` —
clearly not real car models (likely misc/non-car service entries or data-entry noise).
`vehicle_brand_id` is null in every sample row I inspected (old brand assignment was never
really used); `color`/`model_year` are null in **100% of the 1,936 rows** — nothing to carry
over there beyond the columns existing.

## Table-by-table mapping

### 1. `roles` (Spatie) — reconcile by **name**, not id
Old: `1=admin, 2=manager, 3=customer, 4=user, 5=employee`.
New (current): `1=admin, 2=manager, 3=user, 4=customer` — **user/customer are swapped**,
and `employee` doesn't exist yet. Must build old-id→new-id map by matching `name`, creating
`employee` fresh. Do NOT copy ids directly.

### 2. `payment_modes` — reconcile by name
Old has 4 (cash, jazz_cash, easy_paisa, bank_transfer); new only has `cash` (id 1). Create
the missing 3, map old id → new id by name.

### 3. `service_types` — reconcile by name
Old: Water Only, Simple Wash, Full Service, General Service, Deep Clean, Full Detailing.
New (current, from this session's test data): Water Only, Wash, Full Service — partial,
inexact overlap ("Wash" ≠ "Simple Wash"). Plan: match case-insensitively where names align
exactly ("Water Only", "Full Service"), create the other 4 fresh. **New service types now
require category-based rates** (`service_category_rates`) that didn't exist historically —
old services' `charges` were simply typed in per row, so no historical rate data needs
migrating there; the rate matrix stays as whatever you configure going forward.

### 4. `expense_types` — direct create
New table is currently empty. Just insert old's 6 rows, get new ids, map by name.

### 5. `users` — mostly direct field carryover
`id, name, email, phone, address, user_type, is_active, email_verified_at, password,
remember_token, deleted_at, created_at, updated_at` all carry over as-is. New-only columns
(`user_address_id`, `user_pic`, `cnic_pic`, `cnic_number`) → `NULL` (no historical source).
**Must generate new ids** (existing new users occupy ids 1, 2, 3, 11) — build `old_user_id →
new_user_id` map.
Caveat found: old `user_type` and old `model_has_roles` don't perfectly agree (1936 users
have `user_type=3` but only 1935 have a matching `model_has_roles` row; similar for
employee). This mismatch already existed in old data — I'll preserve `user_type` as a raw
value and separately replay `model_has_roles` exactly as it was, rather than trying to
"fix" the historical inconsistency.
Email collisions: check needed against the 4 existing new users' emails before insert
(unlikely to collide given old emails are `xxx@test.com`/real domains, but will verify
programmatically during the actual migration, not just assume).

### 6. `vehicles` (old) → new `vehicles` (find-or-create catalog) + new `user_vehicles`
For each of the 1,936 old rows:
- Normalize `name` (lowercase/trim), try to match against the **existing 582-row catalog**
  by name first (e.g. old "corolla" should match existing "Corolla"); if no match, create a
  new catalog row (brand unknown → `vehicle_brand_id = NULL`, category unknown →
  `vehicle_category_id = NULL`, matching the same gap the existing 582 rows already have).
  Track `old_vehicle_name → new_vehicle_catalog_id`.
- Create one `user_vehicles` row per old row: `user_id` (remapped), `vehicle_id` (resolved
  catalog id above), `registration_number`, `color` (null), `model_year` (null),
  `service_count`, `deleted_at`, `created_at`, `updated_at`. New-only fields
  (`user_pic`/`cnic_pic`/`cnic_number`/`vehicle_pic`) → `NULL`.
- Track `old_vehicle_id (per-car) → new_user_vehicle_id` — this is the mapping `services`
  and `fines` need.

### 7. `services` — direct field carryover + new-column defaults
`service_type_id`, `payment_mode_id` need old→new id remap (per #1–3 above).
`vehicle_id` (old) → `user_vehicle_id` (new, via #6's mapping).
Direct carryover: `diesel, polish, charges, discount, discount_reason, collected_amount,
payment_status, complain, luster, vaccum, deleted_at, created_at, updated_at`.
New-only columns, defaulted since no historical concept existed: `overtime = 0`,
`overtime_amount = 0`, `discount_type = 'amount'` (old discount was always a flat number),
`discount_value = ` the same value as `discount` (so editing an old service in the new UI
shows the right pre-filled value).

### 8. `expenses` — direct carryover
`expense_type_id`, `payment_mode_id`, `user_id` remapped per above; everything else 1:1.

### 9. `fines` — carryover, but with an unresolved schema gap (see questions below)
`user_id` remapped. `amount`/`reason`/timestamps carry over directly. `vehicle_id`: **old
`fines.vehicle_id` pointed at the old "one row per car" vehicles table** — the same entity
that becomes `user_vehicles`, not the new shared `vehicles` catalog. But the *current* `Fine`
model's `vehicle()` relation still points at the catalog `Vehicle` via `vehicle_id`, which
was never updated when `Service` got its `user_vehicle_id` split. This needs a decision
(see below) before fines can be migrated correctly.

### Not migrated (no old data exists for these — new-only concepts)
`vehicle_brands` (582-catalog already has 48 real brands seeded — no need to touch),
`vehicle_categories`, `service_category_rates`, `service_addon_rates`, `user_addresses`,
`temp_vehicles`, `employee_details`, `employee_salary_details`, `amount_transactions`,
`permissions`/`permission_modules`/`role_has_permissions` (AclSeeder already owns these,
old system's tables were empty anyway).

## Migration order (respects FKs)

`roles` (reconcile) → `payment_modes` (reconcile) → `service_types` (reconcile) →
`expense_types` (create) → `users` (create, mapped ids) → `model_has_roles` (replay, mapped
ids) → `vehicles` catalog (find-or-create per distinct name) → `user_vehicles` (create,
mapped ids) → `services` (create, mapped ids) → `expenses` (create, mapped ids) → `fines`
(create, mapped ids — pending the gap above).

## Open questions before I write any migration code

1. **Fines table gap** — should I add a `user_vehicle_id` column to `fines` (mirroring
   `services`) so old fines can correctly reference the specific customer/car they were
   about, rather than an ambiguous shared catalog vehicle? This is only 4 old rows, so low
   stakes, but the relationship would be semantically wrong otherwise.
2. **Vehicle catalog name-matching** — for old names that don't cleanly match the existing
   582-row catalog (`bike`, `rikshaw`, `loader`, `carpet`, `all day sale`, etc. — clearly not
   car models), should I still create catalog rows for them as-is (preserving the odd data
   faithfully), or would you rather I flag these as a separate cleanup list for you to review
   before they become permanent catalog entries?
3. **Duplicate-phone / customer identity** — this session's newer code treats phone number as
   a soft identity key (duplicate-phone warnings). Old users have no such constraint — some
   old phone numbers may repeat across "different" old user rows (e.g. family members sharing
   one number, or data-entry duplicates). Should migration just carry every user over as a
   distinct row regardless (safest, preserves history exactly), or attempt any dedup?
4. **Should this run against a fresh copy of the database first** (e.g. a staging DB
   restored from your current production DB) rather than directly against whatever's live
   right now, given the discovery that live data already has real seeded content I don't
   want to put at risk?

I have not written any migration/seeder code yet, per your instructions — only this analysis
and this mapping document. Let me know your answers above and I'll proceed to the actual
migration command (Laravel Artisan command reading from a secondary DB connection to
`old_db.sql`, chunked, wrapped in transactions, with a full validation report at the end).
