# ARKA MineOps — Action Plan (Rencana Implementasi Teknis)

> **Turunan dari** `docs/concept.md` (v0.3). Dokumen ini adalah **panduan eksekusi** — cukup spesifik agar developer atau AI agent (cursor-agent, composer-2.5) bisa mengerjakan tiap fase tanpa ambiguitas.
> **Sifat proyek:** Greenfield (belum ada kode). **Working dir:** `/home/deahermes/daily-production`.
> **Bahasa:** istilah bisnis Bahasa Indonesia (`produksi`, `pemakaian_bahan_bakar`), istilah teknis English (`Controller`, `Service`, `Model`).

---

## 0. Ringkasan Roadmap & Estimasi

| Fase | Judul | Estimasi | Dependensi | Output MVP? |
|------|-------|----------|------------|-------------|
| **Fase 0** | Project Scaffold & Foundation | 5 hari | — | ✅ |
| **Fase 1** | Master Data & Multi-Site | 7 hari | Fase 0 | ✅ |
| **Fase 2** | Daily Data Entry Core | 12 hari | Fase 1 | ✅ |
| **Fase 3** | Dashboard & Reporting | 10 hari | Fase 2 | ✅ |
| **Fase 4** | Plan vs Actual | 7 hari | Fase 2, 3 | — |
| **Fase 4B** | Procurement KPI (ARK-GS) | 7 hari | Fase 3 (paralel dg Fase 5) | — |
| **Fase 5** | Mobile/PWA & Offline | 7 hari | Fase 2 | — |
| **Fase 6** | Notification & AI | 7 hari | Fase 3, 4 | — |
| **Fase 7** | UAT, Migrasi Data & Rollout | 5 hari | semua | — |

**MVP = Fase 0–3 (± 6–7 minggu).** Total lengkap ± 13–15 minggu.

### Konvensi global (WAJIB diikuti di semua fase)

| Aspek | Aturan | Contoh |
|-------|--------|--------|
| Tabel | `snake_case`, plural | `daily_entries`, `production_records` |
| Foreign key | `singular_id` | `equipment_id`, `pit_id`, `daily_entry_id` |
| Pivot table | urut alfabetis singular | `permission_role`, `project_site` |
| Money | `decimal(14,2)` | `price_per_liter` |
| Kuantitas produksi | `decimal(14,2)` | `ob_removal_bcm`, `coal_getting_ton` |
| Enum | PHP `enum` backed (string), label Indonesia | `EntryStatus::Draft` → "Draf" |
| Tanggal | `date` (harian) / `datetime` (timestamp event) | `production_date` (date) |
| Model | singular PascalCase | `DailyEntry`, `FuelRecord` |
| Controller | singular + `Controller` | `SiteController`, `DailyEntryController` |
| Service | singular + `Service`, di `app/Services/` | `CalculationService` |
| Enum class | di `app/Enums/` | `EntryStatus`, `ShiftName` |
| React component | PascalCase, 1 file 1 komponen | `KpiCard.tsx` |
| Inertia page dir | `kebab-case`, pola `Index/Create/Edit/Show.tsx` | `resources/js/Pages/sites/Index.tsx` |
| Route name | resourceful | `sites.index`, `daily-entries.store` |
| Route URI | kebab-case plural | `/sites`, `/daily-entries` |

### Daftar tabel MySQL milik MineOps (final, 18 tabel)

> Equipment **BUKAN** tabel MineOps (via REST API arkfleet-next). `equipment_types` & `equipment_readings` **dihapus** dari skema (ada di arkfleet-next). KPI procurement (`arkgs_*`) **BUKAN** tabel (shape respons API ARK-GS).

**Auth & akses (Fase 0):** `users`, `password_reset_tokens`, `sessions`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`, `personal_access_tokens`, `cache`, `jobs`, `job_batches`, `failed_jobs`, `audits`.

**Domain (Fase 0–1):** `sites`, `pits`, `shifts`, `fuel_types`, `fuel_prices`, `equipment_assignments`, `project_site_mappings`.

**Domain (Fase 2):** `daily_entries`, `production_records`, `fuel_records`, `equipment_deployments`, `site_info`, `fuel_receipts`, `fuel_stock_movements`.

**Domain (Fase 4):** `monthly_plans`, `plan_targets`.

---

## Fase 0 — Project Scaffold & Foundation

### 1. Goal
Menyiapkan skeleton Laravel 11 + Inertia + React + Ant Design 5 yang jalan dengan auth login, semua migrasi tabel inti, RBAC 4 role, struktur folder standar, dan CI dasar.

### 2. Deliverables (checklist)
- [ ] Laravel 11 terpasang, `php artisan serve` + `npm run dev` jalan.
- [ ] Login/logout bekerja (Breeze Inertia+React+TypeScript).
- [ ] Ant Design 5 + ProTable ter-render di halaman contoh.
- [ ] Semua migrasi tabel inti (domain) ter-migrate tanpa error.
- [ ] Spatie Permission terpasang, 4 role + permission ter-seed.
- [ ] Enums, base Service, base structure ada.
- [ ] Koneksi DB kedua `arkfleet_next` (read-only dev) + seeder equipment dev.
- [ ] Git repo + `.gitignore` + GitHub Actions CI (pint + pest + build).
- [ ] `.env.example` lengkap (DB, Redis, arkfleet-next, ARK-GS placeholder).

### 3. Files to create

**Scaffold command (jalankan berurutan):**
```bash
composer create-project laravel/laravel:^11.0 .
composer require laravel/breeze --dev
php artisan breeze:install react --typescript --pest
```

**Config / bootstrap:**
- `bootstrap/app.php` — daftarkan middleware alias (`role`, `permission`, `HandleInertiaRequests`), tanpa membuat `Http/Kernel.php`.
- `bootstrap/providers.php` — hanya `AppServiceProvider` (JANGAN buat provider baru).
- `config/services.php` — tambah blok `arkfleet` & `arkgs` (base_url, token).
- `config/database.php` — tambah koneksi `arkfleet_next` (dev-only, read-only) untuk seeder.

**Enums (`app/Enums/`):**
- `EntryStatus.php` — `Draft` ("Draf"), `Submitted` ("Disubmit"), `Approved` ("Disetujui").
- `EntrySource.php` — `Manual` ("Manual"), `ExcelImport` ("Impor Excel").
- `ShiftName.php` — `Day` ("Siang"), `Night` ("Malam").
- `ProductionActivity.php` — `OB`, `Coal`, `TopSoil`, `MUD`, `HighAshCoal`.
- `FuelUsageCategory.php` — `WasteLoading`, `WasteHauling`, `Dewatering`, `General`.
- `PitOwner.php` — `GPK` ("GPK"), `ARKA` ("ARKA").
- `PlanMetric.php` — `OB`, `Coal`, `StrippingRatio`.
- `UserRole.php` — `Admin`, `Supervisor`, `Management`, `FuelOfficer`.
- `StockMovementType.php` — `In` ("Masuk"), `Out` ("Keluar").

Setiap enum backed string + method `label(): string` (label Indonesia) + `public static function options(): array` untuk dropdown React.

**Models (`app/Models/`)** — dibuat via `php artisan make:model -m`:
- `Site`, `Pit`, `Shift`, `FuelType`, `FuelPrice`, `EquipmentAssignment`, `ProjectSiteMapping`,
- `DailyEntry`, `ProductionRecord`, `FuelRecord`, `EquipmentDeployment`, `SiteInfo`,
- `FuelReceipt`, `FuelStockMovement`, `MonthlyPlan`, `PlanTarget`.
- (User sudah ada dari Breeze — tambahkan `HasRoles`, `HasApiTokens`, `AuditableTrait`.)

**Services (`app/Services/`)** — skeleton kosong dulu, diisi fase berikut:
- `CalculationService.php` (Fase 2), `EquipmentApiService.php` (Fase 1), `ProcurementApiService.php` (Fase 4B), `ReportService.php` (Fase 3).

**Base Inertia setup:**
- `app/Http/Middleware/HandleInertiaRequests.php` — share `auth.user`, `auth.permissions`, `sites` (yang bisa diakses), `activeSite`, `flash`.
- `resources/js/app.tsx` — bootstrap Inertia + `ConfigProvider` AntD (locale `id_ID`, theme token).
- `resources/js/Layouts/AuthenticatedLayout.tsx` — sidebar menu (Dashboard, Entry, Produksi, Fuel, Equipment, Plan, Procure, Reports, Master, Users) + **site selector** di navbar.
- `resources/js/types/index.d.ts` — tipe `User`, `Site`, `Pit`, `PageProps`.
- `resources/js/lib/http.ts` — wrapper axios untuk API internal (react-query).

**CI:**
- `.github/workflows/ci.yml` — jobs: `composer install` → `pint --test` → `pest` → `npm ci` → `npm run build`.
- `.gitignore` — pastikan `/vendor`, `/node_modules`, `.env`, `/public/build`, `/storage/*.key`.

### 4. Files to modify
- `.env` / `.env.example` — set `DB_CONNECTION=mysql`, `DB_DATABASE=daily_production`, `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`, `SESSION_DRIVER=database`, + `ARKFLEET_BASE_URL`, `ARKFLEET_TOKEN`, `ARKGS_BASE_URL`, `ARKGS_TOKEN`, `ARKFLEET_NEXT_DB_*` (dev seeder).
- `vite.config.js` — pastikan alias `@` → `resources/js`, plugin react.
- `tsconfig.json` — path alias `@/*`.

### 5. Database changes (migrations)

> Tabel Breeze/Laravel default (`users`, `sessions`, `cache`, `jobs`) sudah ada. Yang perlu ditambah:

**`create_permission_tables`** — dari `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`.

**`create_sites_table`:**
```
id, code (string, unique)         // 022C, 021C, 017C, 011C, 025C, 026C, 023C, APS
name (string)                     // GPK, SBI, KPUC, Kitadin
location (string, nullable)
is_active (boolean, default true)
timestamps
```

**`create_pits_table`:**
```
id, site_id (FK->sites, cascade), code (string)   // PIT1, PIT2
owner (enum PitOwner: GPK/ARKA)
is_active (boolean, default true), timestamps
unique(site_id, code)
```

**`create_shifts_table`:**
```
id, name (enum ShiftName), start_time (time), end_time (time)
timestamps
```

**`create_fuel_types_table`:**
```
id, name (string)   // Solar, Bio Solar
is_active (boolean, default true), timestamps
```

**`create_fuel_prices_table`:**
```
id, fuel_type_id (FK), price_per_liter (decimal 14,2), effective_date (date)
timestamps
index(fuel_type_id, effective_date)
```

**`create_equipment_assignments_table`** (cache lokal referensi equipment arkfleet-next):
```
id
equipment_id (unsignedBigInteger)    // referensi ke arkfleet_next.equipment.id (BUKAN FK DB)
unit_code (string)                   // cached: "E 071"
description (string, nullable)       // cached: "Excavator Hitachi EX1200-6"
plant_type_name (string, nullable)   // cached: Digger/Hauler/Support/Heavy Equip
project_code (string)                // cached: 022C, dst.
site_id (FK->sites)
pit_id (FK->pits, nullable)
is_active_for_tracking (boolean, default true)
synced_at (datetime, nullable)       // kapan cached fields di-refresh dari API
timestamps
unique(equipment_id, site_id)
index(site_id, is_active_for_tracking)
```

**`create_project_site_mappings_table`** (pemetaan `project_code` ↔ `site_id`, §9.7.4):
```
id, project_code (string, unique), site_id (FK->sites), timestamps
```

**`create_daily_entries_table`:**
```
id
uuid (uuid, unique)                  // idempotent submit dari PWA (§9.4)
production_date (date)
site_id (FK->sites)
created_by (FK->users)
approved_by (FK->users, nullable)
status (enum EntryStatus, default draft)
source (enum EntrySource, default manual)
source_file (string, nullable)       // path arsip Excel asli
submitted_at (datetime, nullable), approved_at (datetime, nullable)
timestamps
unique(production_date, site_id)
index(production_date, site_id)
```

**`create_production_records_table`:**
```
id, daily_entry_id (FK cascade), pit_id (FK), shift_id (FK)
ob_removal_bcm (decimal 14,2, default 0)
coal_getting_ton (decimal 14,2, default 0)
coal_hauling_ton (decimal 14,2, default 0)
activity (enum ProductionActivity, nullable)
truck_count (int, default 0)
timestamps
index(daily_entry_id), index(pit_id, shift_id)
```

**`create_fuel_records_table`:**
```
id, daily_entry_id (FK cascade)
equipment_id (unsignedBigInteger)    // ref arkfleet_next.equipment.id
unit_code (string, nullable)         // cached
shift_id (FK), fuel_type_id (FK)
liters (decimal 14,2), working_hours (decimal 8,2, nullable)
usage_category (enum FuelUsageCategory, nullable)
timestamps
index(daily_entry_id), index(equipment_id)
```

**`create_equipment_deployments_table`:**
```
id, daily_entry_id (FK cascade)
equipment_id (unsignedBigInteger), unit_code (string, nullable)  // cached
pit_id (FK), shift_id (FK)
prod_ob_bcm (decimal 14,2, default 0), prod_coal_ton (decimal 14,2, default 0)
operator_name (string, nullable)
timestamps
index(daily_entry_id), index(equipment_id)
```

**`create_site_info_table`:**
```
id, daily_entry_id (FK cascade, unique)
weather (string, nullable), rain_hours (decimal 5,2, default 0), slippery_hours (decimal 5,2, default 0)
manpower_plan (int, nullable), manpower_actual (int, nullable)
safety_notes (text, nullable), fuel_stock_liters (decimal 14,2, nullable)
timestamps
```

**`create_fuel_receipts_table`:**
```
id, site_id (FK), receipt_date (date), liters (decimal 14,2)
gi_number (string, nullable), supplier (string, nullable), timestamps
```

**`create_fuel_stock_movements_table`:**
```
id, fuel_receipt_id (FK, nullable), site_id (FK)
type (enum StockMovementType: in/out), liters (decimal 14,2), movement_date (date)
timestamps
```

**`create_monthly_plans_table`:**
```
id, site_id (FK), year (int), month (int), created_by (FK->users), timestamps
unique(site_id, year, month)
```

**`create_plan_targets_table`:**
```
id, monthly_plan_id (FK cascade), pit_id (FK)
metric (enum PlanMetric: OB/Coal/StrippingRatio), owner (enum PitOwner: GPK/ARKA)
target_value (decimal 14,2), timestamps
index(monthly_plan_id)
```

**`create_audits_table`** — dari `php artisan vendor:publish --provider="OwenIt\Auditing\AuditingServiceProvider"`.

**Seeders (`database/seeders/`):**
- `RolePermissionSeeder.php` — 4 role + permission granular (lihat Fase 1 §7 matrix).
- `AdminUserSeeder.php` — user admin default.
- `SiteSeeder.php` — 8 site (022C GPK, 021C SBI, 017C KPUC, 011C Kitadin, 025C, 026C, 023C, APS).
- `PitSeeder.php` — PIT1/PIT2 GPK untuk 022C (minimal).
- `ShiftSeeder.php` — Day (06:00–18:00), Night (18:00–06:00).
- `FuelTypeSeeder.php` + `FuelPriceSeeder.php` — Solar, Bio Solar + harga awal.
- `ProjectSiteMappingSeeder.php` — mapping project_code → site.
- `ArkfleetNextDevSeeder.php` — **dev only**: seed equipment dummy ke DB `arkfleet_next` (mirror struktur `equipment`: id, unit_code, description, plant_type_id, project_code, unitstatus_id, is_active) untuk 022C GPK (~80 unit), agar EquipmentApiService bisa dites lokal tanpa app arkfleet-next asli.
- `DatabaseSeeder.php` — panggil semua di atas berurutan.

### 6. Package dependencies

**composer:**
```bash
composer require inertiajs/inertia-laravel:^2.0
composer require spatie/laravel-permission:^6.0
composer require maatwebsite/excel:^3.1
composer require barryvdh/laravel-dompdf:^3.0
composer require laravel/horizon:^5.0
composer require laravel/sanctum:^4.0
composer require laravel-notification-channels/telegram:^5.0
composer require owen-it/laravel-auditing:^13.0
composer require predis/predis:^2.0
composer require --dev laravel/breeze:^2.0
composer require --dev laravel/pint:^1.0
composer require --dev pestphp/pest:^3.0 pestphp/pest-plugin-laravel:^3.0
composer require --dev barryvdh/laravel-ide-helper:^3.0
```

**npm:**
```bash
npm install @inertiajs/react react react-dom
npm install antd @ant-design/icons @ant-design/pro-components @ant-design/charts
npm install @tanstack/react-query dayjs
npm install -D typescript @types/react @types/react-dom @types/node
npm install -D @vitejs/plugin-react laravel-vite-plugin vite
npm install -D vite-plugin-pwa          # dipakai Fase 5
npm install idb                          # IndexedDB wrapper, dipakai Fase 5
```

### 7. API endpoints
Belum ada endpoint domain di fase ini. Hanya route auth Breeze (`/login`, `/logout`, dsb.).

### 8. Testing
- `pest` smoke test: `GET /login` → 200; login user admin → redirect dashboard.
- `php artisan migrate:fresh --seed` sukses tanpa error.
- Test enum: `EntryStatus::Draft->label() === 'Draf'`.
- Verifikasi `npm run build` sukses (AntD ter-bundle).
- Manual: buka `/dashboard`, sidebar + site selector tampil.

### 9. Dependencies
Tidak ada (fase pertama).

### 10. Estimated effort
**5 hari** (1 dev): scaffold 1h, migrasi+model 2h, RBAC+seeder 1h, layout+AntD+CI 1h.

---

## Fase 1 — Master Data & Multi-Site

### 1. Goal
Admin dapat mengelola seluruh master data multi-site (Sites, PITs, Shifts, Fuel Types/Prices, Users & Roles) dan meng-assign equipment existing dari arkfleet-next (via REST API + cache) ke PIT.

### 2. Deliverables (checklist)
- [ ] CRUD Sites + PITs (nested) dengan ProTable.
- [ ] CRUD Shifts, Fuel Types, Fuel Prices.
- [ ] CRUD Users + assign role, filter per site.
- [ ] `EquipmentApiService` fungsional (fetch, cache Redis TTL 1 jam, fallback).
- [ ] Halaman **Equipment Assignment**: search dari arkfleet-next API → pilih → assign ke PIT.
- [ ] Equipment 022C GPK ter-seed/ter-assign (via dev seeder arkfleet_next).
- [ ] Site selector di navbar berfungsi (ganti site tanpa logout).
- [ ] Permission matrix aktif (route diproteksi middleware `role`/`permission`).

### 3. Files to create

**Controllers (`app/Http/Controllers/`):**
- `SiteController.php` (resource: index/create/store/edit/update/destroy)
- `PitController.php` (resource, scoped by site)
- `ShiftController.php` (resource)
- `FuelTypeController.php` (resource)
- `FuelPriceController.php` (resource)
- `UserController.php` (resource + assign role)
- `RoleController.php` (index/edit permission — opsional untuk admin)
- `EquipmentAssignmentController.php` (index, `store` = assign, `destroy` = unassign, `search` = proxy ke API arkfleet)
- `SiteSwitchController.php` (`store` → set active site di session)

**Services:**
- `app/Services/EquipmentApiService.php`:
  - `search(array $filters): Collection` — `Http::withToken(config('services.arkfleet.token'))->retry(3,100)->get(base.'/api/equipment', $filters)`, cache `Cache::remember("equipment:{project}:{plant}:{active}:{q}", 3600, ...)`.
  - `find(int $id): ?array` — cache `equipment:detail:{id}`.
  - `hmKmReadings(int $id): array` — endpoint `/api/equipment/{id}/hm-km-readings` (dipakai FCR Fase 3).
  - Fallback: `try/catch` → `Cache::get(...)` stale + set flag `stale=true` (graceful degradation §9.6.4).

**Form Requests (`app/Http/Requests/`):**
- `StoreSiteRequest`, `UpdateSiteRequest`, `StorePitRequest`, `UpdatePitRequest`,
- `StoreShiftRequest`, `StoreFuelTypeRequest`, `StoreFuelPriceRequest`,
- `StoreUserRequest`, `UpdateUserRequest`, `AssignEquipmentRequest`.

**Policies (`app/Policies/`):**
- `SitePolicy`, `UserPolicy` (opsional; sebagian besar dilindungi permission middleware).

**Inertia pages (`resources/js/Pages/`):**
- `sites/Index.tsx`, `sites/Create.tsx`, `sites/Edit.tsx`
- `pits/Index.tsx`, `pits/Create.tsx`, `pits/Edit.tsx`
- `shifts/Index.tsx`
- `fuel-types/Index.tsx`
- `fuel-prices/Index.tsx`
- `users/Index.tsx`, `users/Create.tsx`, `users/Edit.tsx`
- `equipment-assignments/Index.tsx` (ProTable list assigned + tombol "Assign Equipment")
- `equipment-assignments/Search.tsx` (modal/drawer: search API arkfleet, filter project_code/plant_type, pilih → assign ke PIT)

**React components (`resources/js/Components/`):**
- `SiteSelector.tsx` (dropdown navbar), `DataTable.tsx` (wrapper ProTable), `FormDrawer.tsx`, `PermissionGate.tsx`.

### 4. Files to modify
- `routes/web.php` — daftarkan resource routes + middleware.
- `app/Http/Middleware/HandleInertiaRequests.php` — inject `sites` accessible + `activeSite` (dari session).
- `app/Models/User.php` — relasi `sites()` (jika akses per-site dibatasi via pivot; opsional tabel `site_user`).

### 5. Database changes
- (Opsional) migrasi `create_site_user_table` (pivot `site_id`+`user_id`) jika akses user dibatasi per site. Nama alfabetis: `site_user`.
- Seeder tambahan: assign equipment 022C GPK ke PIT via `EquipmentAssignmentSeeder` (memanggil dev `arkfleet_next` data → isi `equipment_assignments`).

### 6. Package dependencies
Tidak ada tambahan (semua dari Fase 0).

### 7. API endpoints

**Internal (web routes, Inertia):**
```
GET    /sites                    sites.index
GET    /sites/create             sites.create
POST   /sites                    sites.store
GET    /sites/{site}/edit        sites.edit
PUT    /sites/{site}             sites.update
DELETE /sites/{site}             sites.destroy
(pola sama: pits.*, shifts.*, fuel-types.*, fuel-prices.*, users.*)
GET    /equipment-assignments               equipment-assignments.index
GET    /equipment-assignments/search        equipment-assignments.search   // proxy ke arkfleet API
POST   /equipment-assignments               equipment-assignments.store    // assign ke PIT
DELETE /equipment-assignments/{assignment}  equipment-assignments.destroy
POST   /site-switch                          site-switch.store
```

**Consumed (arkfleet-next REST API, §9.6.1):**
```
GET /api/equipment?project_code=022C&plant_type=&is_active=1&search=E071
GET /api/equipment/{id}
GET /api/equipment/{id}/hm-km-readings
Header: Authorization: Bearer {ARKFLEET_TOKEN}
```
> Dev: dilayani oleh data dev seeder `arkfleet_next` (mock). Prod: app arkfleet-next asli.

### 8. Testing
- Feature test CRUD tiap master (store/update/destroy) sebagai admin → 200/redirect; sebagai supervisor → 403.
- `EquipmentApiService` test: mock `Http::fake()` → search return collection; simulasi API down → fallback ke cache tanpa exception.
- Assign equipment → row masuk `equipment_assignments` dengan cached `unit_code`.
- Site switch → `activeSite` di session berubah, data terfilter.
- **Permission matrix:**

| Permission | admin | supervisor | management | fuel_officer |
|-----------|:---:|:---:|:---:|:---:|
| `master.manage` (sites/pits/shifts/fuel/users) | ✅ | — | — | — |
| `equipment.assign` | ✅ | — | — | — |
| `entry.create` (Fase 2) | ✅ | ✅ | — | ✅(fuel) |
| `entry.approve` (Fase 2) | ✅ | ✅ | — | — |
| `dashboard.view` (Fase 3) | ✅ | ✅ | ✅ | ✅ |
| `plan.manage` (Fase 4) | ✅ | — | ✅ | — |
| `report.generate` (Fase 3) | ✅ | ✅ | ✅ | — |

### 9. Dependencies
Fase 0 selesai.

### 10. Estimated effort
**7 hari**: master CRUD 3h, EquipmentApiService + Assignment 2.5h, site selector + permission 1.5h.

---

## Fase 2 — Daily Data Entry Core

### 1. Goal
Supervisor/fuel officer dapat menginput data harian lengkap (Produksi, Fuel, Equipment Deployment, Info Site) dengan workflow draft→submit→approve, dan Calculation Engine menghitung MTD/YTD/PTD/SR/FCR/Achievement secara konsisten.

### 2. Deliverables (checklist)
- [ ] `DailyEntry` header: create (pilih tanggal+site), lanjut draft, submit, approve.
- [ ] Form Produksi (per PIT per shift): OB, Coal, coal hauling, truck count per aktivitas.
- [ ] Form Fuel (per equipment per shift): liters, working_hours, kategori usage.
- [ ] Form Equipment Deployment (per shift): assignment + prod OB/coal per alat.
- [ ] Form Info Site: cuaca, rain/slippery hours, manpower, safety, fuel stock.
- [ ] `CalculationService`: MTD/YTD/PTD, Stripping Ratio, FCR, Achievement %.
- [ ] Excel Import pipeline (queue job): parse DPR/Daily Info/Fuel → preview → confirm.
- [ ] Status badge + audit trail tiap perubahan.

### 3. Files to create

**Controllers:**
- `DailyEntryController.php` — `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.
- `DailyEntryWorkflowController.php` — `submit`, `approve`, `reject`.
- `ProductionRecordController.php` — `store`/`update` (bulk per entry).
- `FuelRecordController.php` — `store`/`update` (bulk).
- `EquipmentDeploymentController.php` — `store`/`update` (bulk).
- `SiteInfoController.php` — `store`/`update` (upsert per entry).
- `ExcelImportController.php` — `create` (upload form), `store` (dispatch job), `preview`, `confirm`.

**Services:**
- `app/Services/CalculationService.php`:
  - `mtd(int $siteId, Carbon $date, string $metric): float`
  - `ytd(...)`, `ptd(...)` (period-to-date sesuai plan period)
  - `strippingRatio(float $obBcm, float $coalTon): float` = OB / Coal
  - `fcr(int $equipmentId, Carbon $from, Carbon $to): float` = Σ liters / Σ (bcm|ton|working_hours); ambil HM/KM via `EquipmentApiService::hmKmReadings`.
  - `achievement(float $actual, float $target): float` = actual/target*100.
  - Cache hasil agregat di Redis, invalidasi saat entry di-approve.
- `app/Services/DailyEntryService.php` — orchestrate create/upsert child records dalam transaction, generate `uuid`.

**Jobs (`app/Jobs/`):**
- `ParseExcelImportJob.php` (queued) — deteksi jenis (DPR/Info/Fuel), parse via maatwebsite/excel, tulis hasil ke tabel staging/cache untuk preview.
- `CommitExcelImportJob.php` — commit hasil preview yang dikonfirmasi ke tabel domain.

**Excel Import classes (`app/Imports/`):**
- `DprImport.php`, `DailyInfoImport.php`, `FuelReportImport.php` (implements `ToCollection`/`WithHeadingRow`).

**Form Requests:**
- `StoreDailyEntryRequest`, `UpdateProductionRecordsRequest`, `UpdateFuelRecordsRequest`, `UpdateEquipmentDeploymentsRequest`, `UpdateSiteInfoRequest`, `SubmitDailyEntryRequest`, `ApproveDailyEntryRequest`, `ExcelImportRequest`.

**Policies:**
- `DailyEntryPolicy` — `create`, `update` (hanya saat draft & creator/admin), `submit`, `approve` (supervisor/admin, bukan creator sendiri jika perlu).

**Inertia pages:**
- `daily-entries/Index.tsx` (ProTable: filter tanggal/site/status)
- `daily-entries/Create.tsx` (pilih tanggal+site)
- `daily-entries/Edit.tsx` (tab: Produksi / Fuel / Equipment / Info Site — sesuai wireframe §6.2)
- `daily-entries/Show.tsx` (read-only + tombol approve/reject)
- `excel-imports/Create.tsx` (upload)
- `excel-imports/Preview.tsx` (tabel hasil parse + flag error + confirm)

**React components:**
- `entry/ProductionForm.tsx`, `entry/FuelForm.tsx`, `entry/DeploymentForm.tsx`, `entry/SiteInfoForm.tsx`
- `entry/EntryTabs.tsx`, `entry/StatusBadge.tsx`, `entry/EquipmentPicker.tsx` (searchable dropdown → API arkfleet cache).

### 4. Files to modify
- `routes/web.php` — daily-entries resource + workflow + child + excel-import routes.
- `app/Models/DailyEntry.php` — relasi `productionRecords`, `fuelRecords`, `equipmentDeployments`, `siteInfo`, casts enum, `implements Auditable`.
- `bootstrap/app.php` — pastikan queue & schedule terkonfigurasi (jika perlu).

### 5. Database changes
- (Opsional) `create_import_batches_table` untuk tracking status import + hasil preview:
```
id, uuid, user_id, type (dpr/info/fuel), original_filename, stored_path,
status (parsing/preview/committed/failed), parsed_payload (json), row_errors (json), timestamps
```
- Tidak ada perubahan tabel domain (sudah dibuat Fase 0).

### 6. Package dependencies
- Sudah ada (`maatwebsite/excel`, `laravel/horizon`). Pastikan Horizon terpublish: `php artisan horizon:install`.

### 7. API endpoints
```
GET    /daily-entries                       daily-entries.index
GET    /daily-entries/create                daily-entries.create
POST   /daily-entries                       daily-entries.store
GET    /daily-entries/{dailyEntry}          daily-entries.show
GET    /daily-entries/{dailyEntry}/edit     daily-entries.edit
PUT    /daily-entries/{dailyEntry}          daily-entries.update
DELETE /daily-entries/{dailyEntry}          daily-entries.destroy
POST   /daily-entries/{dailyEntry}/submit   daily-entries.submit
POST   /daily-entries/{dailyEntry}/approve  daily-entries.approve
POST   /daily-entries/{dailyEntry}/reject   daily-entries.reject
PUT    /daily-entries/{dailyEntry}/production   production-records.update
PUT    /daily-entries/{dailyEntry}/fuel         fuel-records.update
PUT    /daily-entries/{dailyEntry}/deployment   equipment-deployments.update
PUT    /daily-entries/{dailyEntry}/site-info    site-info.update
GET    /excel-imports/create                 excel-imports.create
POST   /excel-imports                        excel-imports.store
GET    /excel-imports/{batch}/preview        excel-imports.preview
POST   /excel-imports/{batch}/confirm        excel-imports.confirm
```

### 8. Testing
- Create entry → status `draft`, unique(production_date, site_id) enforced (duplicate → error).
- Isi production records → total OB/Coal per PIT terhitung benar di UI.
- Submit → status `submitted`, `submitted_at` terisi; approve → `approved`, cache MTD invalidated.
- `CalculationService` unit test: SR = OB/Coal; MTD = Σ approved entries bulan berjalan; FCR dengan mock hmKmReadings.
- Excel import: upload sample DPR → job parse → preview menampilkan rows + flag baris kosong → confirm → data masuk DB.
- Idempotency: submit dua kali dengan uuid sama tidak menduplikasi.
- Audit: perubahan production_record tercatat di `audits`.

### 9. Dependencies
Fase 1 selesai (butuh sites, pits, shifts, fuel types, equipment assignments).

### 10. Estimated effort
**12 hari**: header+workflow 2.5h, 4 form 4h, CalculationService 2.5h, Excel import pipeline 3h.

---

## Fase 3 — Dashboard & Reporting

### 1. Goal
Manajemen dapat melihat Executive Dashboard real-time (KPI + trend + utilization + per-PIT), Fuel Dashboard, drill-down, serta generate daily & custom report ke PDF/Excel dengan template resmi.

### 2. Deliverables (checklist)
- [ ] Executive Dashboard: 4 KPI card (OB, Coal, SR, Fuel) dengan MTD & Achievement.
- [ ] Chart tren 30 hari (OB/Coal/SR) via `@ant-design/charts`.
- [ ] Equipment utilization & status (Active/Breakdown/Standby).
- [ ] Produksi per-PIT (bar chart).
- [ ] Fuel Dashboard: konsumsi per equipment + FCR trend + breakdown kategori.
- [ ] Drill-down: KPI → PIT → Shift → Equipment.
- [ ] Daily report PDF/Excel (template header ARKA/ENG/IV/12.01).
- [ ] Custom period report (filter site/PIT/date range/equipment).

### 3. Files to create

**Controllers:**
- `DashboardController.php` — `index` (executive), `fuel` (fuel dashboard).
- `DashboardDataController.php` — endpoint JSON untuk react-query (kpi, trend, utilization, per-pit, drilldown).
- `ReportController.php` — `daily` (generate daily PDF/Excel), `custom` (form + generate), `download`.

**Services:**
- `app/Services/DashboardService.php` — kumpulkan KPI/trend/utilization dari `CalculationService` + `EquipmentApiService`, cache Redis.
- `app/Services/ReportService.php` — build data report, render PDF (dompdf) & Excel (maatwebsite export).

**Exports (`app/Exports/`):**
- `DailyReportExport.php`, `CustomPeriodReportExport.php`.

**Views (PDF, `resources/views/reports/`):**
- `daily.blade.php` (template resmi), `custom.blade.php`.
> Buat via `php artisan make:view reports.daily`.

**Inertia pages:**
- `dashboard/Index.tsx` (executive — sesuai wireframe §6.1)
- `dashboard/Fuel.tsx`
- `reports/Index.tsx` (pilih jenis report)
- `reports/Custom.tsx` (form filter)

**React components:**
- `dashboard/KpiCard.tsx`, `dashboard/TrendChart.tsx`, `dashboard/PerPitChart.tsx`,
- `dashboard/EquipmentStatus.tsx`, `dashboard/FcrTrendChart.tsx`, `dashboard/DrilldownDrawer.tsx`.

### 4. Files to modify
- `routes/web.php` — dashboard + dashboard-data (API) + reports routes.
- `resources/js/Layouts/AuthenticatedLayout.tsx` — link Dashboard/Fuel/Reports aktif.

### 5. Database changes
- (Opsional, performa) `create_production_aggregates_table` (materialized summary, di-refresh saat approve):
```
id, site_id, pit_id, production_date, metric, value, period_type (daily/mtd/ytd)
unique(site_id, pit_id, production_date, metric, period_type)
```
> Bisa ditunda; awal cukup on-the-fly + Redis cache.

### 6. Package dependencies
- `@ant-design/charts` (sudah), `barryvdh/laravel-dompdf` (sudah), `maatwebsite/excel` (sudah).

### 7. API endpoints
```
GET /dashboard                     dashboard.index      (Inertia)
GET /dashboard/fuel                dashboard.fuel       (Inertia)
GET /api/dashboard/kpi             dashboard-data.kpi   (JSON, react-query)  ?site_id=&date=
GET /api/dashboard/trend           dashboard-data.trend                     ?site_id=&days=30
GET /api/dashboard/utilization     dashboard-data.utilization
GET /api/dashboard/per-pit         dashboard-data.perPit
GET /api/dashboard/drilldown       dashboard-data.drilldown ?level=pit|shift|equipment&...
GET /reports                        reports.index        (Inertia)
GET /reports/daily                  reports.daily        ?site_id=&date=&format=pdf|excel
GET /reports/custom                 reports.custom       (Inertia form)
POST /reports/custom                reports.customGenerate
GET /reports/download/{file}        reports.download
```
> JSON dashboard endpoints diproteksi Sanctum/`auth` + `permission:dashboard.view`.

### 8. Testing
- KPI endpoint return OB/Coal/SR/Fuel + MTD + achievement untuk site+date; nilai cocok dg CalculationService.
- Trend endpoint return 30 titik data.
- Utilization: hitung status equipment dari data + arkfleet API (mock).
- Report PDF: generate daily report → file PDF valid, header template muncul.
- Report Excel: kolom sesuai; filter custom (date range, PIT) menyaring benar.
- Drill-down: KPI→PIT→Shift→Equipment mengembalikan subset benar.

### 9. Dependencies
Fase 2 selesai (butuh data entry + CalculationService).

### 10. Estimated effort
**10 hari**: dashboard data+cards+charts 4h, fuel dashboard 2h, drill-down 1.5h, report PDF/Excel 2.5h.

---

## Fase 4 — Plan vs Actual

### 1. Goal
Manajemen dapat menginput Monthly Plan (target OB/Coal/SR per PIT per owner GPK/ARKA) dan sistem menghitung Achievement % + variance otomatis, dengan analisis kontribusi rain/slippery terhadap loss produksi.

### 2. Deliverables (checklist)
- [ ] Input Monthly Plan (per site, year, month) + Plan Targets (per PIT, metric, owner).
- [ ] Auto achievement % (Actual/Plan) di dashboard & report.
- [ ] Variance analysis (Plan − Actual, %).
- [ ] Kontribusi rain/slippery ke loss (dari `site_info`).
- [ ] Chart perbandingan Plan vs Actual.

### 3. Files to create

**Controllers:**
- `MonthlyPlanController.php` — resource (index/create/store/edit/update/destroy).
- `PlanTargetController.php` — `store`/`update` (bulk per plan).
- `VarianceController.php` — `index` (analisis variance).

**Services:**
- `app/Services/PlanService.php` — CRUD plan + targets; `achievement()`, `variance()`, `lossContribution()` (korelasi rain/slippery hours vs shortfall).

**Form Requests:**
- `StoreMonthlyPlanRequest`, `UpdatePlanTargetsRequest`.

**Inertia pages:**
- `monthly-plans/Index.tsx`, `monthly-plans/Create.tsx`, `monthly-plans/Edit.tsx` (grid target per PIT per metric per owner).
- `variance/Index.tsx` (tabel + chart Plan vs Actual + loss breakdown).

**React components:**
- `plan/PlanTargetGrid.tsx`, `plan/PlanVsActualChart.tsx`, `plan/VarianceTable.tsx`, `plan/LossContributionChart.tsx`.

### 4. Files to modify
- `routes/web.php` — monthly-plans + plan-targets + variance routes.
- `app/Services/CalculationService.php` — `achievement()` sudah ada; tambah integrasi target dari `plan_targets`.
- `app/Services/DashboardService.php` — inject achievement dari plan ke KPI card.

### 5. Database changes
- Tidak ada tabel baru (`monthly_plans`, `plan_targets` sudah dibuat Fase 0).
- Seeder contoh: `MonthlyPlanSeeder` (plan bulan berjalan 022C).

### 6. Package dependencies
Tidak ada tambahan.

### 7. API endpoints
```
GET    /monthly-plans                    monthly-plans.index
GET    /monthly-plans/create             monthly-plans.create
POST   /monthly-plans                    monthly-plans.store
GET    /monthly-plans/{plan}/edit        monthly-plans.edit
PUT    /monthly-plans/{plan}             monthly-plans.update
DELETE /monthly-plans/{plan}             monthly-plans.destroy
PUT    /monthly-plans/{plan}/targets     plan-targets.update
GET    /variance                          variance.index   ?site_id=&year=&month=
GET    /api/variance/data                 variance.data
```

### 8. Testing
- Create plan + targets → tersimpan per PIT/metric/owner.
- Achievement = actual MTD / target * 100 (unit test).
- Variance = target − actual; loss contribution memakai rain/slippery hours.
- Dashboard KPI card menampilkan achievement dari plan aktif.

### 9. Dependencies
Fase 2 (actual data) + Fase 3 (dashboard) selesai.

### 10. Estimated effort
**7 hari**: plan CRUD 2.5h, achievement/variance service 2h, charts 2.5h.

---

## Fase 4B — Procurement KPI Integration (ARK-GS)

### 1. Goal
MineOps mengonsumsi KPI procurement/material (PO Sent, GRPO, NPI, Budget/CAPEX) dari ARK-GS via REST API + Redis cache (TTL ~6 jam) + fallback, menampilkan Combined Operational View (produksi + procurement) dengan indikator "last synced".

### 2. Deliverables (checklist)
- [ ] `ProcurementApiService` (HTTP Client + retry + cache TTL 6h + fallback graceful).
- [ ] 4 kartu KPI: Budget Performance, GRPO Completion, NPI Efficiency, CAPEX.
- [ ] Combined Operational View (produksi + procurement, filter project_code/site/year/month).
- [ ] Indikator "last synced" tiap kartu.
- [ ] Mapping `project_code ↔ site_id` dipakai (§9.7.4).
- [ ] Fallback: ARK-GS down → tampil cache + warning, dashboard produksi tetap normal.

> **Prasyarat eksternal:** endpoint ARK-GS harus ada dulu. Signature yang diharapkan tercantum di §7 bawah. Dev: gunakan `Http::fake()` / mock server sampai ARK-GS asli siap.

### 3. Files to create

**Controllers:**
- `ProcurementController.php` — `index` (dashboard procurement), `combined` (combined view).
- `ProcurementDataController.php` — JSON: `poSent`, `grpo`, `npi`, `budget` (untuk react-query).

**Services:**
- `app/Services/ProcurementApiService.php`:
  - `poSent(string $projectCode, int $year, int $month): array`
  - `grpo(...)`, `npi(...)`, `budget(...)`
  - Pola: `Cache::remember("arkgs:po-sent:{code}:{y}:{m}", 21600, fn() => Http::withToken(config('services.arkgs.token'))->retry(3,100)->get(...)->json())`
  - Fallback `try/catch` → `Cache::get(...)` stale + `last_synced_at` + flag warning.

**Inertia pages:**
- `procurement/Index.tsx` (kartu Budget/GRPO/NPI/CAPEX + bar charts per project).
- `dashboard/Combined.tsx` (atau integrasi ke `dashboard/Index.tsx` — combined view §6.1).

**React components:**
- `procurement/BudgetCard.tsx`, `procurement/GrpoCard.tsx`, `procurement/NpiCard.tsx`, `procurement/CapexCard.tsx`,
- `procurement/LastSyncedBadge.tsx`, `procurement/PoVsGrpoChart.tsx`, `procurement/NpiInOutChart.tsx`.

### 4. Files to modify
- `config/services.php` — blok `arkgs` (base_url, token) — sudah ditambah Fase 0, verifikasi.
- `routes/web.php` — procurement routes + JSON data routes.
- `resources/js/Layouts/AuthenticatedLayout.tsx` — menu "Procure".
- `resources/js/Pages/dashboard/Index.tsx` — sisipkan 4 kartu procurement (combined view).

### 5. Database changes
- Tidak ada tabel `arkgs_*` (data via API). `project_site_mappings` sudah ada (Fase 0).

### 6. Package dependencies
Tidak ada tambahan (Laravel HTTP Client bawaan).

### 7. API endpoints

**Consumed (ARK-GS, §9.7.1) — semua filter `project_code`, `year`, `month`, respons sertakan `last_synced_at`:**
```
GET /api/kpi/po-sent   → { project_code, year, month, po_amount, budget_amount, budget_pct, last_synced_at }
GET /api/kpi/grpo      → { project_code, po_amount, grpo_amount, completion_pct, status, last_synced_at }
GET /api/kpi/npi       → { project_code, incoming_qty, outgoing_qty, npi_index, last_synced_at }
GET /api/kpi/budget    → { project_code, type(regular/capex), budget_amount, actual_amount, utilization_pct, last_synced_at }
Header: Authorization: Bearer {ARKGS_TOKEN}
```
Threshold: GRPO ≥80 Good / 60–80 Attention / <60 Critical. NPI ≤0.85 Excellent / ≤1.0 Good / ≤1.2 Average / ≤1.5 Below / >1.5 Critical.

**Internal:**
```
GET /procurement                    procurement.index
GET /dashboard/combined             dashboard.combined
GET /api/procurement/po-sent        procurement-data.poSent   ?site_id=&year=&month=
GET /api/procurement/grpo           procurement-data.grpo
GET /api/procurement/npi            procurement-data.npi
GET /api/procurement/budget         procurement-data.budget
```

### 8. Testing
- `ProcurementApiService` dengan `Http::fake()`: return shape benar; API down → fallback cache + warning, tidak throw.
- Cache TTL 6h dihormati (2 call dalam window → 1 HTTP call).
- `project_code` tidak ada mapping → ditandai (tidak crash combined view).
- Kartu render threshold warna benar (GRPO 84% hijau; NPI 0.92 hijau).
- "last synced" timestamp tampil.

### 9. Dependencies
Fase 3 (dashboard) selesai. Bisa **paralel dengan Fase 5**.

### 10. Estimated effort
**7 hari**: ProcurementApiService 2h, kartu+chart 3h, combined view 2h.

---

## Fase 5 — Mobile/PWA & Offline

### 1. Goal
Supervisor dapat menginput data harian dari HP di site meski sinyal jelek: form responsive/touch-friendly, PWA installable, draft tersimpan offline (IndexedDB), auto-sync idempotent saat online.

### 2. Deliverables (checklist)
- [ ] Semua form entry responsive (mobile-first) — wizard step, keyboard numerik, tombol besar.
- [ ] PWA: service worker + manifest + installable ("Add to Home Screen").
- [ ] IndexedDB simpan draft entry lokal.
- [ ] Sync queue: auto-submit saat online, idempotent via UUID.
- [ ] Indikator status offline ("📶 Offline — 2 entry belum sync") + tombol sync manual.

### 3. Files to create

**Frontend (`resources/js/`):**
- `lib/offline/db.ts` — wrapper `idb`: store `draftEntries`, `syncQueue`.
- `lib/offline/syncQueue.ts` — enqueue/flush, retry, idempotent (uuid).
- `lib/offline/useOnlineStatus.ts` — hook status koneksi.
- `Components/offline/OfflineIndicator.tsx`, `Components/offline/SyncButton.tsx`.
- `Components/entry/EntryWizard.tsx` — versi step-by-step mobile dari form entry.
- `sw.ts` / registrasi service worker (via `vite-plugin-pwa`).

**Backend:**
- `app/Http/Controllers/Api/SyncController.php` — endpoint API (Sanctum) terima batch draft dari PWA, idempotent by `uuid`.

### 4. Files to modify
- `vite.config.js` — tambah `VitePWA({ registerType:'autoUpdate', manifest:{...} })`.
- `resources/js/app.tsx` — register SW + `QueryClient` offline-aware.
- `resources/js/Pages/daily-entries/Edit.tsx` — pakai `EntryWizard` di viewport mobile.
- `routes/api.php` — route sync (Sanctum).
- `app/Http/Controllers/DailyEntryController.php` — pastikan `store` idempotent (cek uuid existing).
- `bootstrap/app.php` — pastikan `routes/api.php` + Sanctum middleware aktif.

### 5. Database changes
- `daily_entries.uuid` sudah ada (Fase 0) untuk idempotency. Tidak ada tabel baru.

### 6. Package dependencies
- `vite-plugin-pwa` (dev, sudah di-install Fase 0), `idb` (sudah).
- Pastikan `laravel/sanctum` terpublish: `php artisan install:api` (Laravel 11) atau publish manual.

### 7. API endpoints
```
POST /api/sync/daily-entries      (Sanctum) — batch upsert draft, idempotent by uuid
GET  /api/sync/status             (Sanctum) — cek entry yang sudah tersync
```

### 8. Testing
- Lighthouse PWA audit: installable, SW terdaftar.
- Simulasi offline (DevTools) → isi draft → tersimpan di IndexedDB → online → auto-sync.
- Idempotency: sync 2x uuid sama → 1 record.
- Responsive: form usable di viewport 360px, keyboard numerik muncul untuk input angka.

### 9. Dependencies
Fase 2 selesai (form entry ada).

### 10. Estimated effort
**7 hari**: responsive/wizard 2.5h, PWA+SW 1.5h, IndexedDB+sync queue 2h, endpoint sync+idempotency 1h.

---

## Fase 6 — Notification & AI

### 1. Goal
Sistem mengirim alert otomatis (achievement < target, fuel anomaly), ringkasan harian ke Telegram, reminder input, dan (opsional) insight naratif via OpenRouter.

### 2. Deliverables (checklist)
- [ ] Achievement alert: notif saat achievement < target (mis. <90%).
- [ ] Fuel anomaly alert: deteksi FCR outlier per equipment.
- [ ] Telegram daily summary bot (auto-send ringkasan harian).
- [ ] Reminder: supervisor belum input data by deadline.
- [ ] (Opsional) OpenRouter AI: narrative summary, anomaly explanation, NL query.

### 3. Files to create

**Notifications (`app/Notifications/`):**
- `AchievementBelowTargetNotification.php` (channel: database, telegram)
- `FuelAnomalyNotification.php`
- `DailySummaryNotification.php`
- `EntryReminderNotification.php`

**Services:**
- `app/Services/AnomalyDetectionService.php` — FCR outlier (z-score/IQR terhadap histori equipment).
- `app/Services/AiInsightService.php` (opsional) — OpenRouter HTTP Client: `narrativeSummary()`, `explainAnomaly()`, `nlQuery()` (NL → agregat).

**Jobs / Scheduled:**
- `app/Jobs/SendDailySummaryJob.php` (queued)
- `app/Console/Commands/CheckAchievementCommand.php`
- `app/Console/Commands/CheckFuelAnomalyCommand.php`
- `app/Console/Commands/SendEntryReminderCommand.php`

**Frontend:**
- `Components/NotificationBell.tsx` (badge + dropdown daftar notif dari DB channel).
- `Pages/notifications/Index.tsx`.
- (Opsional) `Pages/ai/Query.tsx` (NL query interface).

### 4. Files to modify
- `routes/console.php` — schedule: daily summary (mis. 07:00 WITA), reminder (mis. 20:00), anomaly/achievement check (setelah approve / harian).
- `config/services.php` — blok `telegram` (bot token, chat id), `openrouter` (api key, model).
- `.env.example` — `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`, `OPENROUTER_API_KEY`, `OPENROUTER_MODEL`.
- `app/Models/User.php` — `routeNotificationForTelegram()`.

### 5. Database changes
- `create_notifications_table` (Laravel default): `php artisan notifications:table` → migrate.

### 6. Package dependencies
- `laravel-notification-channels/telegram` (sudah, Fase 0). OpenRouter via HTTP Client (no package).

### 7. API endpoints
- OpenRouter (consumed, opsional): `POST https://openrouter.ai/api/v1/chat/completions` (header `Authorization: Bearer {OPENROUTER_API_KEY}`).
- Internal: `GET /notifications` (index), `POST /notifications/{id}/read`.

### 8. Testing
- Achievement command: buat data < target → notif ter-generate (fake notification).
- Anomaly service: inject FCR outlier → terdeteksi.
- Telegram: `Notification::fake()` assert `DailySummaryNotification` sent via telegram channel.
- Reminder: entry belum ada by deadline → reminder terkirim.
- AI (opsional): `Http::fake()` OpenRouter → narrative summary return string.

### 9. Dependencies
Fase 3 (dashboard/KPI) + Fase 4 (plan/achievement) selesai.

### 10. Estimated effort
**7 hari**: notifications+channels 2h, anomaly detection 1.5h, telegram+scheduler 2h, AI opsional 1.5h.

---

## Fase 7 — UAT, Data Migration & Rollout

### 1. Goal
Migrasi data historis Excel batch, rekonsiliasi MTD sistem vs Excel, UAT per role di ≥2 site, training, dual-run, dan go-live.

### 2. Deliverables (checklist)
- [ ] Import data historis Excel bertahap (1–2 bulan terakhir dulu, lalu mundur).
- [ ] Rekonsiliasi: MTD sistem == MTD Excel (dalam toleransi).
- [ ] UAT per role (admin, supervisor, fuel officer, management) di 022C + ≥1 site lain.
- [ ] Training tiap role + dokumen singkat.
- [ ] Dual-run: app generate report → dikirim via email (transisi).
- [ ] Go-live checklist selesai.

### 3. Files to create
- `docs/uat-checklist.md` — skenario per role.
- `docs/go-live-checklist.md` — backup, env prod, queue worker (systemd/supervisor), Horizon, Tailscale, cron `schedule:run`.
- `docs/migration-runbook.md` — urutan import batch + rekonsiliasi.
- `app/Console/Commands/ReconcileMtdCommand.php` — bandingkan MTD sistem vs nilai Excel (dari kolom summary), output laporan selisih.
- `tests/Feature/` — skenario UAT otomatis (happy path per role).

### 4. Files to modify
- `.env` (produksi) — kredensial DB/Redis/arkfleet/arkgs/telegram asli.
- `config/*` — finalisasi cache/queue/session prod.

### 5. Database changes
- Tidak ada skema baru. Jalankan `migrate --force` di prod. Backup sebelum import.

### 6. Package dependencies
Tidak ada tambahan.

### 7. API endpoints
Tidak ada baru. Verifikasi endpoint arkfleet-next & ARK-GS produksi reachable.

### 8. Testing
- Rekonsiliasi command: selisih MTD sistem vs Excel ≤ toleransi (mis. 0.5%).
- UAT: tiap role menyelesaikan flow inti (supervisor input+submit, supervisor approve, management lihat dashboard+report, fuel officer input fuel).
- Smoke test produksi: login, dashboard load, generate report, notif Telegram terkirim.
- Load: import batch besar tidak nge-hang UI (queue jalan).

### 9. Dependencies
Semua fase inti (0–4) selesai; 4B/5/6 sesuai scope go-live.

### 10. Estimated effort
**5 hari**: import+rekonsiliasi 2h, UAT+training 2h, dual-run+go-live 1h.

---

## Lampiran A — Ringkasan Semua Composer Packages

| Package | Versi | Fase | Kegunaan |
|---------|-------|------|----------|
| `laravel/framework` | ^11.0 | 0 | Framework |
| `inertiajs/inertia-laravel` | ^2.0 | 0 | Inertia server adapter |
| `laravel/breeze` (dev) | ^2.0 | 0 | Auth scaffold (React+TS) |
| `spatie/laravel-permission` | ^6.0 | 0 | RBAC |
| `maatwebsite/excel` | ^3.1 | 0/2/3 | Import/Export Excel |
| `barryvdh/laravel-dompdf` | ^3.0 | 0/3 | PDF report |
| `laravel/horizon` | ^5.0 | 0/2 | Monitor queue |
| `laravel/sanctum` | ^4.0 | 0/5 | Token API (PWA/bot/service) |
| `laravel-notification-channels/telegram` | ^5.0 | 0/6 | Notif Telegram |
| `owen-it/laravel-auditing` | ^13.0 | 0/2 | Audit trail |
| `predis/predis` | ^2.0 | 0 | Redis client |
| `laravel/pint` (dev) | ^1.0 | 0 | Code style |
| `pestphp/pest` (dev) | ^3.0 | 0 | Testing |
| `pestphp/pest-plugin-laravel` (dev) | ^3.0 | 0 | Testing Laravel |
| `barryvdh/laravel-ide-helper` (dev) | ^3.0 | 0 | DX |

## Lampiran B — Ringkasan Semua NPM Packages

| Package | Fase | Kegunaan |
|---------|------|----------|
| `@inertiajs/react` | 0 | Inertia React |
| `react`, `react-dom` | 0 | React |
| `antd` | 0 | UI kit AntD 5 |
| `@ant-design/icons` | 0 | Icons |
| `@ant-design/pro-components` | 0/1 | ProTable/ProForm |
| `@ant-design/charts` | 3 | Charts (G2Plot) |
| `@tanstack/react-query` | 0/3 | Data fetching/polling |
| `dayjs` | 0 | Tanggal |
| `idb` | 5 | IndexedDB (offline) |
| `typescript` (dev) | 0 | TS |
| `@types/react`,`@types/react-dom`,`@types/node` (dev) | 0 | Tipe |
| `@vitejs/plugin-react` (dev) | 0 | Vite React |
| `laravel-vite-plugin` (dev) | 0 | Vite Laravel |
| `vite` (dev) | 0 | Build |
| `vite-plugin-pwa` (dev) | 5 | PWA/SW |

## Lampiran C — Peta Route ↔ Modul Konsep

| Modul (concept §4) | Fase | Route prefix |
|--------------------|------|--------------|
| MO-Master | 1 | `/sites`, `/pits`, `/shifts`, `/fuel-types`, `/fuel-prices`, `/users`, `/equipment-assignments` |
| MO-Entry | 2 | `/daily-entries`, `/excel-imports` |
| MO-Dashboard | 3 | `/dashboard`, `/api/dashboard/*` |
| MO-Procurement (C2) | 4B | `/procurement`, `/api/procurement/*` |
| MO-Plan | 4 | `/monthly-plans`, `/variance` |
| MO-Report | 3 | `/reports` |
| MO-Notify | 6 | `/notifications` |
| MO-AI | 6 | `/ai/query` (opsional) |

## Lampiran D — Catatan Eksekusi untuk AI Agent

1. **Ikuti user rules Laravel 11:** gunakan `php artisan make:*` (jangan `mkdir`/`touch`); migrasi pivot urut alfabetis; middleware/alias di `bootstrap/app.php`; tidak ada `Http/Kernel.php`; view via `php artisan make:view`.
2. **Jangan buat service provider baru** — cukup `AppServiceProvider`.
3. **Enum backed string** dengan label Indonesia via method `label()`.
4. **Equipment BUKAN tabel** — selalu via `EquipmentApiService` (arkfleet-next) + cache Redis; simpan cached fields di tabel lokal.
5. **Procurement KPI BUKAN tabel** — selalu via `ProcurementApiService` (ARK-GS) + cache Redis TTL 6h.
6. **Kerjakan fase berurutan**; jangan mulai fase N sebelum dependensinya selesai (lihat tiap §9).
7. **Setiap fase**: buat migrasi+model+service dulu (backend), baru controller+route, baru Inertia page+component, terakhir test.
8. **`php artisan migrate:fresh --seed` harus selalu hijau** setelah tiap fase.
