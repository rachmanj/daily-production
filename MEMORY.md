**Purpose**: AI's persistent knowledge base for project context and learnings
**Last Updated**: 2026-07-30

## Memory Maintenance Guidelines

### Structure Standards

- Entry Format: ### [ID] [Title (YYYY-MM-DD)] ✅ STATUS
- Required Fields: Date, Challenge/Decision, Solution, Key Learning
- Length Limit: 3-6 lines per entry (excluding sub-bullets)
- Status Indicators: ✅ COMPLETE, ⚠️ PARTIAL, ❌ BLOCKED

### File Management

- Archive Trigger: When file exceeds 500 lines or 6 months old
- Archive Format: `memory-YYYY-MM.md`
- New File: Start fresh with current date and carry forward only active decisions

---

## Project Memory Entries

### [012] CCR Hourly ↔ Daily Entry — Read-Only Totals Integration (2026-07-30) ✅ COMPLETE

**Decision**: Surface live hourly totals inside Daily Entry Show/Edit for CCR sites without auto-syncing into `production_records` (SR/FCR/dashboard KPIs unchanged).

**Solution**: `HourlyProductionService::getDailyTotals()` aggregates per-entry (any status, not approval-gated); `DailyEntryController::entryPayload()` adds `ccrEnabled` + `hourlyTotals` props; new `HourlySummaryTab` component in EntryTabs/EntryWizard + header badge on Show/Edit.

**Key Learning**: `CalculationService::materialDtd()` is approval-gated and site-wide — wrong for draft entry preview. Per-entry aggregation must be a separate method that sums `hourly_production_records` for one `daily_entry_id` only.

### [011] CCR Hourly — Extended to 017C/022C + Overburden Material (2026-07-30) ✅ COMPLETE

**Decision**: Enable CCR Hourly for coal-mining sites 017C (KPUC) and 022C (GPK) in addition to cement sites 021C/025C; add `MaterialType::Overburden` (`ob`) for hourly OB removal tracking.

**Solution**: Centralized site allowlist in `config/mineops.php` (`ccr_site_codes`); `HourlyEntryController` + `HourlyDashboardController` read from config instead of hardcoded constants. No frontend changes — material/site dropdowns are dynamic from backend props.

**Key Learning**: To onboard a new CCR site: add its code to `config/mineops.php`, assign equipment via Master Data, classify units via **Klasifikasi CCR** modal, then set `material_daily_plans` (no admin UI yet — DB/seeder only).

### [009] CCR Hourly Module — Material Stream Parallel to OB/Coal (2026-07-29) ✅ COMPLETE

**Decision**: Add `hourly_production_records` + `material_daily_plans` as child of existing `daily_entries`; reuse draft→submit→approve workflow.

**Solution**: `MaterialType` enum; `HourlyProductionService` upsert via unique `(daily_entry_id, equipment_id, material_type, hour_slot)`; `CalculationService::materialDtd/Mtd/hourlyTarget`; IndexedDB `draftHourly` store (DB v2).

**Key Learning**: CCR sites (021C, 025C, 017C, 022C — see `config/mineops.php`) equipment columns come from `equipment_assignments` with `material_type` + `display_order` — no hardcoded unit list in frontend. Admins set these via Equipment Assignment → **Klasifikasi CCR** modal (`equipment-assignments.update`).

### [010] Equipment Assignment CCR Classification UI (2026-07-30) ✅ COMPLETE

**Challenge**: Hourly Entry grid empty ("Belum ada alat ter-assign") because assign flow only set `pit_id`/`is_active_for_tracking`; `material_type` was seeder-only.

**Solution**: `UpdateEquipmentAssignmentRequest` + `EquipmentAssignmentController::update`; `ClassifyModal.tsx` on index table for `material_type`, `equipment_role`, `display_order`, tracking toggle.

**Key Learning**: Assign (ArkFleet search) and classify (CCR metadata) are separate steps — unit may be assigned before its CCR material role is known.

### [008] Consolidated Report — Multi-Site Period Rollup (2026-07-25) ✅ COMPLETE

**Decision**: Add "Laporan Konsolidasi" as a dedicated reports page (not merging Dashboard/Reports nav) that rolls up production, fuel, deployment, and site-info across multiple sites and a date range into one on-screen view plus merged PDF/Excel export.

**Solution**: `DashboardService::consolidated()` for live KPIs/trend; `ReportService::buildConsolidatedReportData()` for export payload; 5-sheet Excel (`Summary`, `Production`, `Fuel`, `Deployment`, `Site Info`); Blade `reports/consolidated.blade.php` with per-site sections. Empty `site_ids` = all active sites.

**Key Learning**: Daily exports already loaded fuel/deployment in `buildDailyReportData` but did not render them — consolidation closes the gap the original 3-Excel workflow intended, without replacing per-site daily DPR downloads.

---

### [007] Daily Entry Shifts SQL & React Double Mount (2026-07-25) ✅ COMPLETE

**Challenge**: Viewing/editing daily entries crashed with `Unknown column 'site_id' in shifts`. Browser also logged `createRoot()` called twice, causing intermittent blank pages.

**Solution**: `shifts` table is global (Day/Night only) — removed invalid `site_id`/`is_active` filters in `DailyEntryController::entryPayload`. Fixed duplicate Vite entry in `app.blade.php` (load only `app.tsx`) and made `app.tsx` reuse a single React root across HMR reloads.

**Key Learning**: Shifts are not site-scoped in this schema; match `ShiftController` query pattern. Inertia apps should not list page components as separate `@vite` entries.

---

### [006] Chinese Date Locale in Tables (2026-07-25) ✅ COMPLETE

**Challenge**: Daily Entry table showed dates like `31 5月 2026` (Chinese month) despite `dayjs.locale('id')` in `app.tsx`.

**Solution**: Ant Design Pro bundles `zh_CN` dayjs locale; separate Vite page entry chunks can use a different dayjs instance. Added `@/lib/date` with `formatDate`/`formatDateTime` that always call `.locale('id')`, deduped `dayjs` in `vite.config.js`, and updated date displays to use the helper.

**Key Learning**: With `@vite([app.tsx, page.tsx])` multi-entry setup, set locale per-format call or share a single configured dayjs module — global `dayjs.locale()` in app entry may not reach page chunks.

---

### [005] Daily Entry authorize() Error (2026-07-25) ✅ COMPLETE

**Challenge**: `DailyEntryController::index` crashed with `Call to undefined method authorize()` at line 29.

**Solution**: Base `Controller` was empty (Laravel 11+ skeleton). Added `AuthorizesRequests` trait so `$this->authorize()` works in all controllers (`DailyEntryController`, `DailyEntryWorkflowController`, `ExcelImportController`).

**Key Learning**: When using policies with `$this->authorize()`, ensure base `Controller` includes `Illuminate\Foundation\Auth\Access\AuthorizesRequests`.

---

### [004] Theme System & Username Login (2026-07-25) ✅ COMPLETE

**Decision**: Global dark/light mode via React `ThemeContext` (default dark, `localStorage` persistence) driving both Ant Design `ConfigProvider` algorithm and Tailwind `dark` class on `<html>`. Login redesigned as split-screen branded page using Ant Design components.

**Solution**: Added nullable unique `username` column to `users`. Login accepts single `login` field; `LoginRequest` resolves email vs username via `filter_var`. Admins manage usernames via Master Data > Pengguna Create/Edit forms.

**Key Learning**: Mixed Ant Design + Tailwind apps need dual theming — Ant tokens for component UI, Tailwind `dark:` for any remaining utility-based pages. Inline `<head>` script prevents theme flash on load.

---

### [001] Project Kickoff — Concept & Action Plan Finalized (2026-07-23) ✅ COMPLETE

**Challenge**: Design an integrated mining operations dashboard to replace 3 daily Excel reports (DPR, Daily Info Site, Fuel Report) sent via email, plus integrate procurement KPIs from SAP B1.

**Solution**: Created comprehensive concept document (v0.3, ~800 lines) with 8 modules, 18-table ERD, and detailed action plan (9 phases, 1,059 lines). Three-system architecture: SAP B1 → ARK-GS (procurement), arkfleet-next (equipment), ARKA MineOps (production) — all via REST API.

**Key Learning**: Equipment data should NOT be duplicated — arkfleet-next already has ~1,000 units with codes matching Excel. Procurement KPIs (PO Sent, GRPO, NPI) already synced from SAP B1 by ARK-GS. ARKA MineOps consumes both via API, focuses on production operations + unified dashboard.

---

### [002] External Integration Architecture (2026-07-23) ✅ COMPLETE

**Decision**: All external data via REST API (not shared DB). ARKA MineOps consumes:
- `GET /api/equipment` from arkfleet-next (cached 1h)
- `GET /api/kpi/po-sent`, `/grpo`, `/npi`, `/budget` from ARK-GS (cached 6h)

**Key Learning**: Redis caching + graceful degradation (show last known data when API down). Equipment stored as `equipment_id` FK reference only — no column duplication (unit_code, model, etc. read from API when needed for display).

---

### [003] Database Conventions Locked (2026-07-23) ✅ COMPLETE

**Conventions**: Tables in snake_case plural (`daily_entries`, `production_records`), FKs as `singular_id`, money as `decimal(14,2)`, all tables have `id` PK + timestamps. Business terms in Indonesian, code in English.

**Key Learning**: Calculation Engine computes MTD/YTD/PTD/SR/FCR/Achievement — never stored as raw columns. Single source of truth for operational numbers.