# ARKA MineOps — Konsep Aplikasi Integrasi Laporan Operasional Tambang

> **Dokumen Konsep (Brainstorming)** · Greenfield Project
> PT. Arkananta · **Enterprise-wide, multi-site** — mencakup seluruh site operasional (022C Graha Panca Karsa, 021C SBI, 017C KPUC, 011C Kitadin, dan site lain), bukan hanya satu site tunggal.
> Versi 0.3 · Status: Draft konsep (belum coding) — revisi: integrasi via REST API + multi-site + integrasi procurement/material KPI dari ARK-GS (SAP B1)

---

## 1. Nama Aplikasi

**ARKA MineOps** — *Integrated Mining Operations Dashboard*

Alternatif nama (jika perlu opsi):

| Nama | Tagline | Kesan |
|------|---------|-------|
| **ARKA MineOps** ✅ | "One Platform, Every Site" | Profesional, langsung ke inti (mining operations), mencerminkan skala enterprise-wide/multi-site |
| **PitPulse** | "Denyut nadi tambang, real-time" | Modern, energik, cocok untuk dashboard |
| **KarsaOps** | "Operasional dalam genggaman" | Lokal, mengambil dari "Graha Panca Karsa" |
| **CoalBoard** | "Semua laporan dalam satu papan" | Deskriptif, mudah diingat |

**Rekomendasi:** `ARKA MineOps`. Nama perusahaan sebagai brand equity + "MineOps" langsung menjelaskan domain. Nama internal modul memakai kode `MO-*` (mis. `MO-Production`, `MO-Fuel`).

**Filosofi produk:** *"Input sekali, dilihat semua, real-time."* Menggantikan siklus **Excel → Email → Download → Merge manual** dengan **satu sumber kebenaran (single source of truth)**.

---

## 2. Arsitektur Overview

### 2.1 Masalah Inti

Saat ini ada **3 aliran laporan terpisah** yang datang via email sebagai file Excel:

```
DPR (Produksi)  ─┐
Daily Info Site ─┼─→ Email Harian ─→ Manajemen download & merge manual ❌
Fuel Report     ─┘
```

Ketiganya sebenarnya **berbagi entitas yang sama**: tanggal, shift, PIT, dan yang paling penting — **equipment (alat berat)**. Inilah kunci integrasi.

### 2.2 Prinsip Integrasi: "Equipment & Time sebagai Poros"

Ketiga laporan bertemu pada dua dimensi:

- **Dimensi Waktu:** `tanggal` + `shift (Day/Night)`
- **Dimensi Aset:** `equipment` (E 071, A 40 G, T 112, dll.) di `PIT` tertentu

```mermaid
graph TB
    subgraph SUMBER["3 Sumber Laporan (Legacy Excel)"]
        DPR["DPR<br/>Daily Production Report"]
        DIS["Daily Info Site<br/>Laporan Pagi"]
        FUEL["Fuel Report<br/>Pemakaian Solar"]
    end

    subgraph CORE["ARKA MineOps — Core Data Model"]
        TIME["Dimensi Waktu<br/>Tanggal + Shift"]
        ASSET["Dimensi Aset<br/>Equipment + PIT"]
        PROD["Production Records"]
        FUELREC["Fuel Records"]
        EQREC["Equipment Deployment"]
    end

    subgraph OUTPUT["Output Terintegrasi"]
        DASH["Dashboard Real-time"]
        REP["Auto-generated Reports<br/>PDF / Excel"]
        ALERT["Alert & Notifikasi"]
        API["API / Telegram Bot"]
    end

    DPR -->|input/upload| PROD
    DIS -->|input/upload| EQREC
    FUEL -->|input/upload| FUELREC

    PROD --> TIME & ASSET
    FUELREC --> TIME & ASSET
    EQREC --> TIME & ASSET

    TIME & ASSET --> DASH & REP & ALERT & API
```

### 2.3 Arsitektur Teknis (High-Level)

```mermaid
graph LR
    subgraph CLIENT["Client Layer"]
        WEB["Web App<br/>Inertia + React + AntD"]
        MOBILE["Mobile Browser / PWA<br/>Supervisor di site"]
    end

    subgraph SERVER["Application Layer — Laravel 11"]
        HTTP["HTTP / Inertia Controllers"]
        SVC["Domain Services<br/>Production, Fuel, Equipment"]
        CALC["Calculation Engine<br/>MTD/YTD/PTD, SR, FCR, Achievement"]
        IMPORT["Excel Import Pipeline<br/>Queue Jobs"]
        EXPORT["Report Export<br/>PDF / Excel"]
        NOTIF["Notification & Alert Engine"]
    end

    subgraph DATA["Data Layer"]
        MYSQL[("MySQL 8")]
        REDIS[("Redis<br/>cache + queue")]
        FILES["File Storage<br/>Excel arsip + PDF"]
    end

    subgraph EXT["Integrasi Eksternal"]
        ARKFLEET["arkfleet-next<br/>REST API (equipment)"]
        ARKGS["ARK-GS<br/>REST API (procurement/material KPI)<br/>← sync SAP B1"]
        TG["Telegram / WhatsApp"]
        AI["OpenRouter API<br/>(opsional: anomaly & insight)"]
    end

    WEB & MOBILE --> HTTP
    HTTP --> SVC --> CALC
    HTTP --> IMPORT & EXPORT
    SVC --> MYSQL
    SVC -->|"HTTP call<br/>+ cache Redis"| ARKFLEET
    SVC -->|"HTTP call<br/>+ cache Redis"| ARKGS
    CALC --> REDIS
    IMPORT --> FILES & MYSQL
    NOTIF --> TG
    SVC -.opsional.-> AI
    NOTIF --> REDIS
```

**Integrasi tiga sistem (three-system integration):** ARKA MineOps berperan sebagai **unified dashboard** yang menyatukan tiga sumber data lewat REST API — data **produksi** (native, input harian), data **equipment** (dari arkfleet-next), dan data **procurement/material** (dari ARK-GS yang men-sync dari SAP B1). Ketiganya dikonsumsi via pola yang konsisten: Laravel HTTP Client + cache Redis + graceful degradation.

```mermaid
graph LR
    SAP[("SAP B1<br/>SQL Server")] -->|"sync 2x/hari<br/>(SapService)"| ARKGS["ARK-GS<br/>(ERP dashboard)"]
    ARKGS -->|"REST API<br/>PO/GRPO/NPI/Budget"| MINEOPS["ARKA MineOps<br/>(Unified Dashboard)"]
    ARKFLEET["arkfleet-next<br/>(Fleet Mgmt)"] -->|"REST API<br/>equipment/HM-KM"| MINEOPS
    PROD["Input Produksi Harian<br/>(DPR/Fuel/Info Site)"] -->|native| MINEOPS
    MINEOPS --> USERS["Manajemen & Supervisor<br/>(produksi + procurement dalam 1 layar)"]
```

**Keputusan arsitektur kunci:**

1. **Calculation Engine terpusat** — MTD/YTD/PTD, Stripping Ratio, FCR, dan Achievement % dihitung di backend secara konsisten (tidak lagi tersebar di formula Excel yang rawan salah). Data mentah harian disimpan, agregat dihitung/di-cache.
2. **Import Pipeline berbasis Queue** — parsing Excel besar (778×156) jalan di background job agar UI tidak nge-hang.
3. **Data mentah vs. agregat dipisah** — tabel harian menyimpan angka aktual per hari; kolom MTD/YTD di Excel lama TIDAK disimpan mentah, melainkan diturunkan (derived) agar selalu konsisten.
4. **Equipment bukan data baru** — ARKA MineOps **tidak membuat ulang** master equipment. Data alat sudah ada & terkelola rapi di aplikasi **arkfleet-next** (fleet management existing PT. Arkananta), jadi MineOps **mengintegrasikan**, bukan mendupikasi. Lihat §2.4.
5. **Integrasi via REST API, bukan shared database** — ARKA MineOps dan arkfleet-next tetap **dua aplikasi independen**: masing-masing bisa di-*deploy* dan di-*scale* terpisah, tanpa *coupling* skema database satu sama lain. Komunikasi murni lewat HTTP/REST + caching. Lihat §2.4.
6. **Site sebagai first-class citizen** — aplikasi dirancang **enterprise-wide** untuk seluruh site PT. Arkananta, bukan hanya satu site. Setiap `daily_entries` sudah terikat ke `site_id`, dan pemilihan site adalah elemen utama navigasi UI (site selector di navbar/dashboard) — lihat §6.
7. **Procurement & material KPI di-*consume*, bukan di-*sync* ulang** — data pengadaan (PO, GRPO) dan efisiensi material (NPI, Budget) sudah di-sync dari **SAP B1** oleh aplikasi **ARK-GS** (existing). ARKA MineOps **tidak menduplikasi sync SAP**, melainkan mengonsumsi KPI-nya **via REST API** dengan pola yang sama seperti arkfleet-next (HTTP Client + Redis cache + fallback). Ini menjadikan MineOps sebagai *unified dashboard* produksi + procurement. Lihat §2.5.

### 2.4 Integrasi dengan arkfleet-next — Equipment Registry

**Temuan penting:** aplikasi **arkfleet-next** sudah punya master data equipment yang lengkap (~1.000 unit lintas project, tersebar di berbagai site — termasuk ~80+ unit khusus di Site 022C GPK) dengan kode unit (`E 071`, `DZ 040`, `ADT 009`, `T 112`, dll.) yang **persis sama** dengan kode unit yang dipakai di Excel Fuel Report & Daily Info Site. Tabel `arkfleet_next.equipment` bahkan sudah punya `equipment_hm_km_readings` untuk tracking HM/KM — kebutuhan yang tadinya dirancang sebagai tabel `equipment_readings` di §3.

**Keputusan:** ARKA MineOps **tidak membuat tabel `equipment` sendiri**. Equipment adalah *shared master data* — single source of truth ada di arkfleet-next, dan MineOps mengaksesnya **murni via REST API** (bukan membaca tabel database-nya langsung) untuk kebutuhan operasional (fuel, deployment, produksi per alat).

#### 2.4.1 Pendekatan Integrasi: REST API

```mermaid
graph TB
    subgraph FLOW["Integrasi Equipment — REST API (satu-satunya pendekatan)"]
        A1["ARKA MineOps<br/>(Laravel app)"] -->|"HTTP call<br/>(Laravel HTTP Client)"| A2["arkfleet-next<br/>REST API endpoint"]
        A2 --> A3[("MySQL<br/>arkfleet_next")]
        A1 -.->|"cache hasil API<br/>(TTL 1 jam)"| A4[("Redis")]
        A1 -->|koneksi utama| A5[("MySQL — schema<br/>daily_production")]
    end
```

**Mengapa REST API, bukan shared database:**

| Aspek | Alasan memilih REST API |
|-------|--------------------------|
| Decoupling | Kedua aplikasi tidak saling terikat pada skema database masing-masing; perubahan struktur tabel di satu sisi tidak otomatis memecahkan yang lain. |
| Deploy & scale independen | ARKA MineOps dan arkfleet-next bisa di-*deploy*, di-*update*, dan di-*scale* terpisah, bahkan bisa dipindah ke server berbeda tanpa refactor besar. |
| Kontrak yang jelas | API endpoint menjadi *contract* yang eksplisit dan terdokumentasi (§9.6), lebih mudah diaudit dibanding akses tabel langsung. |
| Keamanan | Tidak perlu memberi akses database lintas aplikasi (kredensial DB terbatas hanya milik masing-masing app); akses cukup lewat token API. |
| Trade-off yang diterima | Ada network round-trip & perlu strategi caching — dimitigasi dengan Redis cache (TTL 1 jam) + fallback graceful degradation (lihat §9.6). |

#### 2.4.2 Prinsip Referensi & Caching Data

- Equipment di ARKA MineOps tetap direferensikan sebagai **`equipment_id` (foreign key reference)** ke `arkfleet_next.equipment.id` — tapi aplikasi **tidak join langsung** ke tabel arkfleet-next; `equipment_id` didapat & divalidasi lewat panggilan API.
- Untuk menghindari API call di setiap render dashboard, MineOps menyimpan **cache/denormalized copy** dari field equipment yang sering diakses (`unit_code`, `description`, `plant_type_name`) di tabel lokal. Ada dua strategi yang bisa dipakai (bisa dikombinasikan):
  1. **Sinkronisasi via event/webhook atau scheduled sync** — cache lokal di-refresh saat arkfleet-next mengirim webhook perubahan, atau via scheduled job berkala.
  2. **API call real-time + caching layer** — data equipment diambil live dari API, tapi hasilnya di-cache di Redis (TTL 1 jam) agar tidak membebani arkfleet-next di setiap request.
- Data **operasional** (shift assignment, produksi per alat, konsumsi fuel) tetap murni domain MineOps karena arkfleet-next tidak punya konteks ini.
- Data **HM/KM** (untuk kalkulasi FCR) diambil via endpoint `GET /api/equipment/{id}/hm-km-readings` dari arkfleet-next — MineOps tidak perlu bikin tabel readings sendiri lagi (lihat perubahan ERD di §3, detail endpoint di §9.6).
- Filtering equipment ke scope site tertentu dilakukan via query parameter `project_code` pada endpoint `GET /api/equipment` (mis. `project_code=022C`, `021C`, `017C`, dst.) — sesuai site yang sedang aktif dipilih user (§6).

### 2.5 Integrasi dengan ARK-GS — Procurement & Material KPIs

**Temuan penting:** PT. Arkananta sudah punya aplikasi **ARK-GS** (`ark-gs-newdb`) — ERP dashboard existing (Laravel 8 + AdminLTE) yang **men-sync data procurement & material dari SAP B1** (SQL Server) secara terjadwal **dua kali sehari (06:05 & 12:05 WITA)** via `SapService`. ARK-GS sudah mengelola KPI yang **melengkapi** data produksi MineOps: **PO Sent, GRPO, NPI, dan Budget** — lintas project (`017C`, `021C`, `022C`, `025C`, `026C`, `APS`, `023C`), berjalan di **VPS yang sama** dengan arkfleet-next dan (rencananya) ARKA MineOps.

**Keputusan:** ARKA MineOps **tidak menduplikasi sync SAP B1**. Sync procurement adalah tanggung jawab ARK-GS (single source of truth untuk data SAP). MineOps **mengonsumsi KPI-nya murni via REST API** — konsisten dengan pendekatan API-first yang dipakai untuk arkfleet-next (§2.4). Dengan begitu, MineOps menjadi **unified dashboard**: KPI produksi (native) + KPI equipment (arkfleet-next) + KPI procurement/material (ARK-GS) dalam satu layar.

#### 2.5.1 Peran ARK-GS (SAP B1 Procurement Sync)

ARK-GS men-sync & menormalkan data SAP B1 ke dalam empat domain KPI:

| Domain KPI | Sumber data ARK-GS | KPI yang dihitung |
|------------|--------------------|-------------------|
| **PO Sent** (Purchase Order) | `powithetas` (raw SAP) → `purchase_orders` + `purchase_order_items` | **PO Sent vs Plant Budget** — % budget yang terserap PO per project |
| **GRPO** (Goods Receipt PO) | `grpos` (dedup key: `po_no`+`grpo_no`+`item_code`) | **PO Sent vs GRPO** — % item PO yang sudah diterima per project (≥80% hijau, <80% merah) |
| **NPI** (Net Production Index) | `incomings` (barang masuk) vs `migis` (material issue) | **Incoming Qty / Outgoing Qty** per project — makin rendah makin efisien |
| **Budget** (Regular + CAPEX) | `budgets` (type: regular/capex, per project, per bulan/tahun) | Budget vs actual spending |

#### 2.5.2 Pendekatan Integrasi: REST API (konsisten dengan arkfleet-next)

```mermaid
graph TB
    subgraph SRC["SAP B1 (SQL Server)"]
        SAP[("DB::connection('sap_sql')")]
    end

    subgraph GS["ARK-GS (existing ERP dashboard)"]
        SYNC["SapService<br/>sync 2x/hari (06:05 & 12:05 WITA)"]
        GSDB[("MySQL ark-gs<br/>powithetas, purchase_orders,<br/>grpos, incomings, migis, budgets")]
        GSAPI["REST API<br/>/api/kpi/po-sent · /api/kpi/grpo<br/>/api/kpi/npi · /api/kpi/budget"]
    end

    subgraph MO["ARKA MineOps"]
        SVC["ProcurementApiService<br/>(Laravel HTTP Client)"]
        CACHE[("Redis Cache<br/>TTL ~6 jam, sesuai jadwal sync")]
        DASH["MO-Procurement Dashboard<br/>+ Combined Operational View"]
    end

    SAP -->|"sync"| SYNC --> GSDB --> GSAPI
    GSAPI -->|"HTTP call (per project/periode)"| SVC
    SVC --> CACHE --> DASH
```

**Data flow:** `SAP B1 → ARK-GS sync (2x/hari) → ARK-GS REST API → ARKA MineOps (HTTP Client + Redis cache) → dashboard procurement + combined view`.

**Mengapa REST API (bukan direct DB read):** meski ARK-GS berada di VPS yang sama (opsi *direct DB read read-only* secara teknis mungkin), pendekatan **REST API tetap dipilih** demi konsistensi dengan integrasi arkfleet-next (§2.4.1) — decoupling skema, deploy/scale independen, kontrak eksplisit, dan keamanan (tidak perlu kredensial DB lintas aplikasi). Endpoint tambahan yang perlu di-*expose* ARK-GS: `GET /api/kpi/po-sent`, `GET /api/kpi/grpo`, `GET /api/kpi/npi`, `GET /api/kpi/budget` (detail di §7.3.2 & §9.7). ARK-GS sudah punya beberapa API controller (`Api/DashboardDailyApiController`, `Api/CapexApiController`, `Api/SupplierController`, `Api/CoalPriceController`), jadi menambah endpoint KPI ini konsisten dengan arsitekturnya.

#### 2.5.3 Prinsip Referensi & Caching Data Procurement

- Data procurement bersifat **read-only** di sisi MineOps — semua sync & normalisasi SAP tetap milik ARK-GS.
- **Kunci join lintas sistem:** `project_code` (mis. `022C`, `021C`, `017C`) yang **konsisten** dengan `site.code` di MineOps dan `project_code` equipment di arkfleet-next — inilah yang memungkinkan *combined view* produksi + procurement per site.
- **Freshness realistis:** karena ARK-GS sync 2x/hari, TTL cache MineOps di-set selaras (mis. ~6 jam) + tampilkan timestamp **"last synced"** agar user tahu kesegaran data (§9.7).
- **Graceful degradation:** bila ARK-GS API down, tampilkan data cache terakhir + warning, tidak gagal total (§9.7) — sama seperti pola arkfleet-next (§9.6.4).

---

## 3. Entity Relationship Diagram (ERD)

Model data dirancang agar ketiga laporan menyatu pada `equipment`, `site/pit`, `shift`, dan `production_date`.

> **Catatan integrasi (lihat §2.4):** `equipment` **tidak lagi dimodelkan sebagai tabel milik MineOps**. Master equipment (`arkfleet_next.equipment`) berada di aplikasi **arkfleet-next** — digambar di ERD sebagai kotak *external reference* (garis putus-putus) yang direferensikan oleh `FUEL_RECORDS` dan `EQUIPMENT_DEPLOYMENTS` via `equipment_id`, diakses **via REST API** (bukan shared DB — lihat §2.4). Tabel `EQUIPMENT_TYPES` dan `EQUIPMENT_READINGS` yang sebelumnya direncanakan **dihapus** dari skema MineOps karena setara `plant_types`/`equipment_hm_km_readings` sudah tersedia di arkfleet-next dan diambil via API.
>
> **Catatan integrasi procurement (lihat §2.5):** KPI procurement/material **bukan tabel milik MineOps** — sumbernya adalah **ARK-GS** (yang men-sync dari SAP B1). Digambar di ERD sebagai kotak *external reference* (garis putus-putus): `ARKGS_PO_SENT`, `ARKGS_GRPO`, `ARKGS_BUDGET` (diakses via REST API dari ARK-GS), dan `ARKGS_NPI` (metrik turunan yang dihitung ARK-GS dari `incomings`/`migis`). Semuanya di-*join* ke data MineOps lewat `project_code` yang konsisten dengan `SITES.code`. MineOps **tidak menyimpan tabel** ini secara mentah — hanya mengonsumsi via API + cache Redis (§7.3.2).

```mermaid
erDiagram
    USERS ||--o{ DAILY_ENTRIES : creates
    ROLES ||--o{ USERS : has

    SITES ||--o{ PITS : contains
    PITS ||--o{ PRODUCTION_RECORDS : "produced at"

    ARKFLEET_EQUIPMENT ||..o{ FUEL_RECORDS : "references (external, via REST API)"
    ARKFLEET_EQUIPMENT ||..o{ EQUIPMENT_DEPLOYMENTS : "references (external, via REST API)"

    SITES ||..o{ ARKGS_PO_SENT : "KPI per project_code (external, ARK-GS API)"
    SITES ||..o{ ARKGS_GRPO : "KPI per project_code (external, ARK-GS API)"
    SITES ||..o{ ARKGS_BUDGET : "KPI per project_code (external, ARK-GS API)"
    SITES ||..o{ ARKGS_NPI : "derived metric per project_code (external, ARK-GS API)"

    SHIFTS ||--o{ PRODUCTION_RECORDS : "measured in"
    SHIFTS ||--o{ FUEL_RECORDS : "measured in"
    SHIFTS ||--o{ EQUIPMENT_DEPLOYMENTS : "assigned in"

    DAILY_ENTRIES ||--o{ PRODUCTION_RECORDS : groups
    DAILY_ENTRIES ||--o{ FUEL_RECORDS : groups
    DAILY_ENTRIES ||--o{ EQUIPMENT_DEPLOYMENTS : groups
    DAILY_ENTRIES ||--|| SITE_INFO : "has weather/safety"

    MONTHLY_PLANS ||--o{ PLAN_TARGETS : contains
    PITS ||--o{ PLAN_TARGETS : "targeted for"

    FUEL_TYPES ||--o{ FUEL_RECORDS : "type of"
    FUEL_TYPES ||--o{ FUEL_PRICES : "priced by"
    FUEL_RECEIPTS ||--o{ FUEL_STOCK_MOVEMENTS : records

    USERS {
        bigint id PK
        string name
        string email
        bigint role_id FK
        boolean is_active
    }
    ROLES {
        bigint id PK
        string name "admin/supervisor/management/fuel_officer"
    }
    SITES {
        bigint id PK
        string code "022C/021C/017C/011C/dst — multi-site"
        string name "GPK/SBI/KPUC/Kitadin/dst (enterprise-wide)"
        string location "berbagai lokasi Kalimantan"
    }
    PITS {
        bigint id PK
        bigint site_id FK
        string code "PIT1/PIT2"
        string owner "GPK/ARKA"
    }
    ARKFLEET_EQUIPMENT {
        bigint id PK "external — via REST API arkfleet-next"
        string unit_code "E 071 (matches Excel codes)"
        string description "Excavator Hitachi EX1200-6"
        bigint unit_model_id FK "external"
        bigint plant_type_id FK "1 Digger/2 Hauler/3 Support/4 Heavy Equip"
        bigint asset_category_id FK "1 Mayor/2 Minor"
        bigint unitstatus_id FK "1 Active/2 Inactive/3 Scrap/4 Sold"
        string project_code FK "022C/021C/017C/dst — site/project code"
        boolean is_active
        boolean is_rfu
    }
    SHIFTS {
        bigint id PK
        string name "Day/Night"
        time start_time
        time end_time
    }
    DAILY_ENTRIES {
        bigint id PK
        date production_date
        bigint site_id FK
        bigint created_by FK
        string status "draft/submitted/approved"
        string source "manual/excel_import"
    }
    PRODUCTION_RECORDS {
        bigint id PK
        bigint daily_entry_id FK
        bigint pit_id FK
        bigint shift_id FK
        decimal ob_removal_bcm
        decimal coal_getting_ton
        decimal coal_hauling_ton
        string activity "OB/Coal/TopSoil/MUD/HighAshCoal"
        int truck_count
    }
    FUEL_RECORDS {
        bigint id PK
        bigint daily_entry_id FK
        bigint equipment_id FK "FK -> arkfleet_next.equipment.id"
        bigint shift_id FK
        bigint fuel_type_id FK
        decimal liters
        decimal working_hours
        string usage_category "WasteLoading/WasteHauling/Dewatering/General"
    }
    EQUIPMENT_DEPLOYMENTS {
        bigint id PK
        bigint daily_entry_id FK
        bigint equipment_id FK "FK -> arkfleet_next.equipment.id"
        bigint pit_id FK
        bigint shift_id FK
        decimal prod_ob_bcm
        decimal prod_coal_ton
        string operator_name
    }
    SITE_INFO {
        bigint id PK
        bigint daily_entry_id FK
        string weather
        decimal rain_hours
        decimal slippery_hours
        int manpower_plan
        int manpower_actual
        text safety_notes
        decimal fuel_stock_liters
    }
    MONTHLY_PLANS {
        bigint id PK
        bigint site_id FK
        int year
        int month
    }
    PLAN_TARGETS {
        bigint id PK
        bigint monthly_plan_id FK
        bigint pit_id FK
        string metric "OB/Coal/StrippingRatio"
        string owner "GPK/ARKA"
        decimal target_value
    }
    FUEL_TYPES {
        bigint id PK
        string name "Solar/Bio Solar"
    }
    FUEL_PRICES {
        bigint id PK
        bigint fuel_type_id FK
        decimal price_per_liter
        date effective_date
    }
    FUEL_RECEIPTS {
        bigint id PK
        date receipt_date
        decimal liters
        string gi_number "No GI"
        string supplier
    }
    FUEL_STOCK_MOVEMENTS {
        bigint id PK
        bigint fuel_receipt_id FK
        string type "in/out"
        decimal liters
        date movement_date
    }
    ARKGS_PO_SENT {
        string project_code "external — join ke SITES.code (via ARK-GS API)"
        int year
        int month
        decimal po_amount "total PO Sent (item_amount)"
        decimal budget_amount "plant budget periode ini"
        decimal budget_pct "PO Sent / Budget (%) — derived"
        datetime last_synced_at "kesegaran data dari SAP B1"
    }
    ARKGS_GRPO {
        string project_code "external — join ke SITES.code (via ARK-GS API)"
        int year
        int month
        decimal po_amount "total PO Sent"
        decimal grpo_amount "total GRPO diterima"
        decimal completion_pct "GRPO / PO Sent (%) — derived"
        string status "Good >=80 / Attention 60-80 / Critical <60"
        datetime last_synced_at
    }
    ARKGS_BUDGET {
        string project_code "external — join ke SITES.code (via ARK-GS API)"
        int year
        int month
        string type "regular/capex"
        decimal budget_amount
        decimal actual_amount "actual spending"
        decimal utilization_pct "actual / budget (%) — derived"
        datetime last_synced_at
    }
    ARKGS_NPI {
        string project_code "external — derived dari incomings/migis (ARK-GS)"
        int year
        int month
        decimal incoming_qty "barang masuk (filtered dept/item)"
        decimal outgoing_qty "material issue (filtered dept/item)"
        decimal npi_index "incoming / outgoing — makin rendah makin efisien"
        datetime last_synced_at
    }
```

> **Catatan ERD external procurement:** keempat entitas `ARKGS_*` di atas **tidak dibuat sebagai tabel MySQL di MineOps** — mereka adalah *shape* respons API dari ARK-GS (hasil agregasi per `project_code` + periode), digambar di ERD hanya untuk memperjelas field yang dikonsumsi. `budget_pct`, `completion_pct`, `utilization_pct`, dan `npi_index` adalah **metrik turunan** (bisa dihitung ARK-GS di API atau MineOps saat render). `ARKGS_NPI` khususnya diturunkan ARK-GS dari tabel `incomings` (barang masuk) & `migis` (material issue) dengan filter `dept_code` (40, 50, 60, 140, 200) dan pengecualian item tertentu (CO%, EX%, FU%, PB%, Pp%, SA%, SO%, SV%).

### 3.1 Catatan Desain Tabel

- **`daily_entries` sebagai "header" harian** — satu record per (tanggal, site). Semua detail (produksi, fuel, deployment, info site) tergantung padanya. Ini yang menggantikan "satu file Excel per hari".
- **Agregat (MTD/YTD/PTD/Achievement) TIDAK disimpan sebagai kolom mentah** — dihitung on-the-fly oleh Calculation Engine + di-cache di Redis. Alternatif untuk performa: tabel `production_aggregates` (materialized summary) yang di-refresh saat data harian di-approve.
- **`equipment` bukan tabel MineOps** — `equipment_id` di `fuel_records` & `equipment_deployments` tetap FK reference ke `arkfleet_next.equipment.id` (lihat §2.4), tapi MineOps **tidak join langsung** ke tabel arkfleet-next. Sebagai gantinya, MineOps menyimpan **cache/denormalized copy** dari field yang sering diakses (`unit_code`, `description`, `plant_type_name`) di tabel lokal agar tidak perlu API call di setiap render dashboard. Sinkronisasi dilakukan via event/webhook atau scheduled sync — atau, alternatifnya, tetap API call real-time dengan caching layer (Redis, TTL 1 jam). Lihat §2.4.2 & §9.6.
- **HM/KM awal-akhir tidak perlu tabel `equipment_readings` sendiri** — sudah tersedia di `arkfleet_next.equipment_hm_km_readings`, diakses via endpoint `GET /api/equipment/{id}/hm-km-readings` (kolom `reading_date`, `reading_type` [hm/km], `reading_value`). MineOps mengambil data ini via API untuk kalkulasi FCR, bukan mencatat ulang atau membaca tabel-nya langsung.
- **Stripping Ratio** = `Σ OB (bcm) / Σ Coal (ton)` — metrik turunan, tidak disimpan mentah.
- **FCR (Fuel Consumption Ratio)** = liter fuel per satuan produksi (bcm/ton) atau per jam kerja — turunan dari `fuel_records` + `production_records`, dengan HM/KM diambil via API dari `arkfleet_next.equipment_hm_km_readings`.

---

## 4. Daftar Modul / Fitur

### Modul A — Master Data Management (`MO-Master`)
| Fitur | Deskripsi |
|-------|-----------|
| ~~Equipment Registry (CRUD)~~ → **Equipment Assignment** | **Tidak perlu dibuat dari nol.** Master unit (kode, tipe, model, ownership, status) sudah ada di **arkfleet-next**. Admin **search/filter equipment via REST API** dari arkfleet-next (per site — mis. ~80+ unit di Site 022C), hasil di-*cache*, lalu **assign ke PIT** & tandai sebagai *active for production tracking*. Lihat §2.4 & §9.6. |
| Site & PIT Config | Kelola **multiple site** (022C GPK, 021C SBI, 017C KPUC, 011C Kitadin, dst. — bukan cuma satu site) dan PIT per site (PIT1 GPK, PIT2 GPK, dst.), kepemilikan area. Admin bisa menambah/mengaktifkan site baru sesuai ekspansi operasional. |
| Shift Definition | Day/Night dengan jam mulai-selesai. |
| Fuel Type & Price | Jenis solar + histori harga per tanggal efektif (untuk kalkulasi biaya). |
| User & Role | Manajemen user + role-based access. |

> **Equipment Assignment (baru):** halaman untuk browse/search equipment via endpoint `GET /api/equipment` dari arkfleet-next (filter by `project_code` sesuai site aktif, `plant_type`, `unitstatus`), pilih unit yang relevan, lalu assign ke PIT + tandai aktif untuk tracking produksi/fuel harian. Saat unit dipilih, MineOps menyimpan `equipment_id` + cached fields (`unit_code`, `description`, `plant_type`) ke tabel lokal. Equipment baru yang ditambahkan admin fleet di arkfleet-next akan muncul di list ini setelah cache di-refresh (via webhook invalidation atau scheduled sync, TTL 1 jam — lihat §9.6) — admin MineOps kemudian meng-assign-nya ke PIT saat unit tersebut mulai beroperasi di site.

### Modul B — Daily Data Entry (`MO-Entry`)
| Fitur | Deskripsi |
|-------|-----------|
| Form Produksi Harian | Input OB Removal & Coal Getting per PIT per shift, truck count per aktivitas. |
| Form Fuel Usage | Input pemakaian solar per equipment per shift + kategori (Waste Loading/Hauling/Dewatering/General). |
| Form Info Site | Cuaca, rain & slippery hours, manpower, safety notes, fuel stock. |
| Form Equipment Deployment | Assignment alat per shift + produksi per alat (dari Daily Info Site). |
| **Excel Import** | Upload file DPR/Daily Info/Fuel → parse otomatis → preview → konfirmasi. Untuk migrasi & workflow transisi. |
| Draft & Submit Workflow | Simpan draft, submit, approve (supervisor → management). |

### Modul C — Dashboard & Analytics (`MO-Dashboard`)
| Fitur | Deskripsi |
|-------|-----------|
| Executive Dashboard | KPI hari ini: OB, Coal, SR, Fuel + MTD & Achievement vs Plan (gauge/progress). |
| Production Trend | Grafik tren OB, Coal, Stripping Ratio (harian/MTD/YTD). |
| Fuel Dashboard | Konsumsi per equipment, FCR trend, breakdown per kategori usage. |
| Equipment Utilization | Availability, working hours, produktivitas per alat. |
| Drill-down | Klik KPI → detail per PIT → per shift → per equipment. |

### Modul C2 — Procurement & Material KPI (`MO-Procurement`)

Modul ini **mengonsumsi KPI dari ARK-GS via REST API** (§2.5) — bukan sync SAP sendiri. Semua data read-only, di-*cache* Redis, dengan indikator "last synced" (§9.7). Bisa berdiri sebagai halaman sendiri **atau** menyatu ke MO-Dashboard sebagai *combined view*.

| Fitur | Deskripsi |
|-------|-----------|
| **Budget Performance** (PO Sent vs Budget) | KPI **% budget terserap PO** per project. Bar chart: Budget (hijau) vs PO Sent (biru) per project + gauge utilisasi budget keseluruhan. Sumber: `GET /api/kpi/po-sent` + `GET /api/kpi/budget`. |
| **GRPO Completion** (GRPO vs PO Sent) | KPI **% item PO yang sudah diterima** per project. Bar chart: PO Sent (biru) vs GRPO (warna sesuai %) + gauge completion. Threshold: **≥80% Good** / **60–80% Attention** / **<60% Critical**. Sumber: `GET /api/kpi/grpo`. |
| **NPI Efficiency** (In/Out ratio) | KPI **efisiensi material** = Incoming Qty / Outgoing Qty per project. Bar chart: Incoming vs Outgoing + gauge NPI index. Threshold (makin rendah makin baik): **≤0.85 Excellent** / **≤1.0 Good** / **≤1.2 Average** / **≤1.5 Below** / **>1.5 Critical**. Sumber: `GET /api/kpi/npi`. |
| **Budget vs Actual (Regular + CAPEX)** | Plant budget (regular & CAPEX) per project per bulan/tahun vs actual spending. Sumber: `GET /api/kpi/budget`. |
| **Combined Operational View** | Satu dashboard yang menampilkan **KPI produksi** (OB/Coal/SR/Fuel) + **KPI procurement** (PO Sent/GRPO/NPI/Budget/CAPEX) berdampingan, filter by **project (site selector), bulan, tahun** — *join* lewat `project_code` yang konsisten dengan `sites.code`. |

> **Catatan integrasi:** modul ini memakai `ProcurementApiService` (Laravel HTTP Client + Redis cache, §7.3.2) dengan pola resiliency/fallback yang sama seperti integrasi arkfleet-next (§9.6.4). Filter `project_code` pada tiap endpoint menyelaraskan KPI procurement dengan site yang aktif dipilih user (§6). Karena ARK-GS sync SAP 2x/hari, data KPI menampilkan timestamp **"last synced"** (§9.7).

### Modul D — Plan vs Actual (`MO-Plan`)
| Fitur | Deskripsi |
|-------|-----------|
| Monthly Plan Input | Target OB/Coal/SR per PIT per bulan (Plan GPK & Plan ARKA). |
| Auto Achievement | Hitung Achievement % otomatis (Actual / Plan). |
| Variance Analysis | Analisis selisih plan vs actual, kontribusi rain/slippery terhadap loss. |

### Modul E — Reporting (`MO-Report`)
| Fitur | Deskripsi |
|-------|-----------|
| Auto-generate Daily Report | Rekap harian format resmi (mirip layout Excel lama) → PDF/Excel. |
| Custom Period Report | Report per rentang tanggal, per PIT, per equipment. |
| Export | PDF (untuk distribusi) & Excel (untuk yang masih butuh olah data). |
| Template resmi | Header dokumen ARKA/ENG/IV/12.01 dipertahankan agar familiar. |

### Modul F — Notification & Alert (`MO-Notify`)
| Fitur | Deskripsi |
|-------|-----------|
| Achievement Alert | Notif saat achievement < target (mis. < 90%). |
| Fuel Anomaly Alert | Deteksi konsumsi fuel abnormal per equipment (FCR outlier). |
| Daily Summary Bot | Auto-kirim ringkasan harian ke Telegram/WhatsApp grup manajemen. |
| Reminder | Ingatkan supervisor jika data harian belum diinput jam tertentu. |

### Modul G — AI Insight (opsional, `MO-AI` via OpenRouter)
| Fitur | Deskripsi |
|-------|-----------|
| Narrative Summary | Ringkasan naratif otomatis ("Produksi hari ini 95% target, tertahan hujan 2 jam di PIT2"). |
| Anomaly Explanation | Jelaskan lonjakan fuel/penurunan produksi. |
| Natural Language Query | "Berapa total OB PIT1 minggu ini?" → jawaban + grafik. |

---

## 5. UX Flow — User Journey

### 5.1 Flow Supervisor (Input Data Harian di Site)

```mermaid
flowchart TD
    A["Login (HP/tablet di site)"] --> B{Data hari ini<br/>sudah ada?}
    B -->|Belum| C["Buat Daily Entry baru<br/>pilih tanggal"]
    B -->|Draft ada| D["Lanjut draft"]
    C --> E["Isi Form Produksi<br/>per PIT per shift"]
    D --> E
    E --> F["Isi Fuel Usage<br/>per equipment"]
    F --> G["Isi Info Site<br/>cuaca, manpower, safety"]
    G --> H{Sinyal ada?}
    H -->|Ya| I["Submit → sync ke server"]
    H -->|Jelek| J["Simpan offline (PWA)<br/>auto-sync saat online"]
    J --> I
    I --> K["Notif ke supervisor senior<br/>untuk approve"]
```

### 5.2 Flow Management (Lihat Dashboard)

```mermaid
flowchart TD
    A["Login"] --> B["Executive Dashboard<br/>KPI hari ini + MTD"]
    B --> C{Perlu detail?}
    C -->|Ya| D["Drill-down per PIT / shift / equipment"]
    C -->|Tidak| E["Cukup lihat ringkasan"]
    D --> F["Generate Report PDF/Excel"]
    B --> G["Terima Daily Summary<br/>di Telegram (push)"]
    F --> H["Share ke stakeholder"]
```

### 5.3 Flow Admin (Setup & Plan)

```mermaid
flowchart TD
    A["Login sbg Admin"] --> B["Setup Master Data<br/>Equipment, PIT, Shift, Fuel"]
    B --> C["Input Monthly Plan<br/>target OB/Coal per PIT"]
    C --> D["Kelola User & Role"]
    D --> E["Monitor & approve data"]
```

### 5.4 Flow Migrasi (Excel Import)

```mermaid
flowchart LR
    A["Upload file Excel<br/>DPR/Info/Fuel"] --> B["Deteksi jenis laporan<br/>+ mapping sheet"]
    B --> C["Queue Job: parse"]
    C --> D["Preview hasil parse<br/>+ validasi/flag error"]
    D --> E{Data valid?}
    E -->|Ya| F["Konfirmasi → simpan ke DB"]
    E -->|Ada error| G["Perbaiki mapping / edit manual"]
    G --> F
    F --> H["Arsipkan file asli"]
```

---

## 6. Wireframe Konsep

### 6.1 Executive Dashboard (Desktop)

```
┌──────────────────────────────────────────────────────────────────────┐
│  ARKA MineOps   Site: [022C GPK ▼]   📅 31 Mei 2026 ▼    🔔 3  👤 Budi │
│                  ┌──────────────────┐                                 │
│                  │ ✓ 022C GPK       │  ← site selector dropdown       │
│                  │   021C SBI       │     (semua site yang bisa       │
│                  │   017C KPUC      │     diakses user)               │
│                  │   011C Kitadin   │                                 │
│                  └──────────────────┘                                 │
├────────────┬─────────────────────────────────────────────────────────┤
│ ▤ Dashboard│  RINGKASAN HARI INI                    [Export ▼] [Report]│
│ ✎ Entry    │ ┌───────────┐┌───────────┐┌───────────┐┌───────────┐    │
│ ⛏ Produksi │ │ OB REMOVAL││COAL GETTING││ STRIPPING ││   FUEL    │    │
│ ⛽ Fuel     │ │ 45.230 Bcm││ 8.120 ton ││  RATIO    ││ 32.400 L  │    │
│ 🚜 Equipment│ │ ▲ 96% plan││ ▲ 92% plan││   5.57    ││ FCR 0.71  │    │
│ 📋 Plan     │ └───────────┘└───────────┘└───────────┘└───────────┘    │
│ 🧾 Procure  │  ─ PROCUREMENT & MATERIAL (ARK-GS · SAP B1) ────────────│
│ 📊 Reports  │ ┌───────────┐┌───────────┐┌───────────┐┌───────────┐    │
│ ⚙ Master    │ │  BUDGET   ││   GRPO    ││    NPI    ││  CAPEX    │    │
│ 👥 Users    │ │ PO/Budget ││ Completion││ In/Out    ││ Realisasi │    │
│             │ │  73% used ││  ● 84% ✓  ││  0.92 ✓   ││  61% used │    │
│             │ │ (gauge)   ││ (≥80 good)││(≤1.0 good)││ (gauge)   │    │
│             │ └───────────┘└───────────┘└───────────┘└───────────┘    │
│             │  ⟳ last synced: 24 Jul 2026 12:05 WITA (ARK-GS)         │
│             │  ┌─────────────────────────┐┌──────────────────────┐   │
│             │  │ TREN OB & COAL (30 hari)││ ACHIEVEMENT MTD      │   │
│             │  │   ╱╲    ╱╲___╱          ││  OB   ███████░░ 88%  │   │
│             │  │  ╱  ╲__╱                ││  Coal ██████░░░ 79%  │   │
│             │  │ (line chart)            ││  (gauge/progress)    │   │
│             │  └─────────────────────────┘└──────────────────────┘   │
│             │  ┌─────────────────────────┐┌──────────────────────┐   │
│             │  │ PRODUKSI PER PIT        ││ STATUS ALAT          │   │
│             │  │ PIT1 GPK  ██████ 24.1k  ││ ● Active     34      │   │
│             │  │ PIT2 GPK  ████   21.1k  ││ ● Breakdown   4      │   │
│             │  │ (bar chart)             ││ ● Standby     2      │   │
│             │  └─────────────────────────┘└──────────────────────┘   │
│             │  ┌─────────────────────────┐┌──────────────────────┐   │
│             │  │ PO SENT vs GRPO per proj││ NPI IN/OUT per proj  │   │
│             │  │ 022C ███████░ 84%       ││ 022C  In 1.2k Out 1.3│   │
│             │  │ 021C █████░░░ 62% ⚠     ││ 021C  In 0.9k Out 1.0│   │
│             │  │ (bar: PO biru / GRPO)   ││ (bar: incoming/outgo)│   │
│             │  └─────────────────────────┘└──────────────────────┘   │
└────────────┴─────────────────────────────────────────────────────────┘
```

**Combined Operational View:** dashboard di atas menyatukan **KPI produksi** (OB/Coal/SR/Fuel — native) dengan **KPI procurement/material** (Budget/GRPO/NPI/CAPEX — dari ARK-GS via API). Empat kartu procurement baru muncul berdampingan dengan kartu produksi, semuanya difilter oleh **site selector** (`project_code`), bulan, dan tahun. Indikator **"last synced"** menampilkan kesegaran data sesuai jadwal sync ARK-GS (2x/hari, §9.7). Warna kartu mengikuti threshold masing-masing KPI (GRPO ≥80% hijau; NPI ≤1.0 hijau).

**Site selector sebagai first-class citizen:** dropdown site di navbar (022C, 021C, 017C, 011C, dst.) muncul di semua halaman utama, bukan hanya dashboard. Memilih site akan memfilter seluruh data (dashboard, entry, report, equipment assignment) ke site tersebut. User dengan akses multi-site bisa berpindah site tanpa logout ulang; daftar site yang muncul disesuaikan dengan hak akses (role) masing-masing user.

### 6.2 Form Daily Entry — Produksi (Desktop)

```
┌──────────────────────────────────────────────────────────────────────┐
│  Daily Entry — 31 Mei 2026            Status: DRAFT    [Simpan][Submit]│
├──────────────────────────────────────────────────────────────────────┤
│  [Produksi] [Fuel] [Equipment] [Info Site]        ← tab               │
├──────────────────────────────────────────────────────────────────────┤
│  OVER BURDEN REMOVAL (Bcm)                                             │
│  ┌────────┬──────────┬──────────┬──────────┬──────────┐               │
│  │  PIT   │ Day Shift│Night Shift│  Total   │ vs Plan  │               │
│  ├────────┼──────────┼──────────┼──────────┼──────────┤               │
│  │PIT1 GPK│ [12.500 ]│ [11.600 ]│  24.100  │  96% ▲   │               │
│  │PIT2 GPK│ [10.800 ]│ [10.330 ]│  21.130  │  94% ▲   │               │
│  └────────┴──────────┴──────────┴──────────┴──────────┘               │
│                                                                        │
│  COAL GETTING (ton)              TRUCK COUNT (per aktivitas)           │
│  ┌────────┬─────────┬─────────┐  ┌──────────────┬───────┐             │
│  │  PIT   │Day │Night│ Total  │  │ Overburden   │ [412] │             │
│  │PIT1    │[..]│[..] │ 4.100  │  │ Top Soil     │ [ 88] │             │
│  │PIT2    │[..]│[..] │ 4.020  │  │ Coal Hauling │ [156] │             │
│  └────────┴─────────┴─────────┘  │ MUD / HighAsh│ [...] │             │
│                                   └──────────────┴───────┘             │
│  ⚠ Auto-calc: MTD & YTD dihitung otomatis saat submit                 │
└──────────────────────────────────────────────────────────────────────┘
```

### 6.3 Mobile View — Supervisor (Input di Site)

```
┌─────────────────────┐   ┌─────────────────────┐
│ ☰  ARKA MineOps   🔔│   │ ← Fuel Entry        │
│ 📅 31 Mei · 022C    │   │ 31 Mei · Day Shift  │
├─────────────────────┤   ├─────────────────────┤
│ ⚡ Entry Hari Ini   │   │ 🔍 Cari equipment.. │
│ ┌─────────────────┐ │   │ ┌─────────────────┐ │
│ │ Produksi    ✓   │ │   │ │ E 071 Hitachi   │ │
│ │ Fuel        ●   │ │   │ │ Liter: [ 850  ] │ │
│ │ Equipment   ○   │ │   │ │ Jam:   [ 11.5 ] │ │
│ │ Info Site   ○   │ │   │ └─────────────────┘ │
│ └─────────────────┘ │   │ ┌─────────────────┐ │
│                     │   │ │ A 40 G          │ │
│ 📶 Offline mode     │   │ │ Liter: [ 420  ] │ │
│ 2 entry belum sync  │   │ │ Jam:   [ 10.0 ] │ │
│ [ Sync sekarang ]   │   │ └─────────────────┘ │
│                     │   │  + Tambah equipment │
│ [   Lanjut Isi  →]  │   │ [    Simpan Draft ] │
└─────────────────────┘   └─────────────────────┘
```

**Prinsip UX mobile:** form pendek per langkah, input numerik dengan keyboard angka, indikator offline yang jelas, tombol besar (sarung tangan/kondisi lapangan), dark-mode friendly untuk terik/malam.

---

## 7. Tech Stack Detail

### 7.1 Backend — Laravel 11+

| Kebutuhan | Package / Approach |
|-----------|-------------------|
| Framework | Laravel 11 (skeleton baru: `bootstrap/app.php`, tanpa Kernel.php) |
| Auth & Session | Laravel Breeze (Inertia+React starter) atau Fortify |
| Role & Permission | `spatie/laravel-permission` |
| Excel Import/Export | `maatwebsite/excel` (PhpSpreadsheet) — parsing DPR/Fuel besar |
| PDF Export | `barryvdh/laravel-dompdf` atau `spatie/laravel-pdf` (Browsershot untuk layout kompleks) |
| Queue | Redis + Laravel Horizon (monitor job import) |
| Cache | Redis (agregat MTD/YTD/FCR) |
| Notifications | Laravel Notifications + channel Telegram (`laravel-notification-channels/telegram`) |
| Query/Reporting | Eloquent + query builder; pertimbangkan DB views untuk agregat berat |
| API (mobile/bot) | Laravel Sanctum (token) untuk endpoint bot & PWA sync |
| Testing | Pest / PHPUnit + factories |
| Audit log | `owen-it/laravel-auditing` (jejak perubahan data operasional) |
| **Integrasi arkfleet-next (API)** | ARKA MineOps mengonsumsi **REST API** dari arkfleet-next (Laravel HTTP Client + cache Redis) untuk data master equipment — bukan shared database. Lihat §2.4 & §7.3.1. |
| **Integrasi ARK-GS (API)** | ARKA MineOps mengonsumsi **REST API** dari ARK-GS (Laravel HTTP Client + cache Redis, pola sama seperti arkfleet-next) untuk KPI procurement/material (PO Sent, GRPO, NPI, Budget) — bukan sync SAP sendiri. Lihat §2.5 & §7.3.2. |

### 7.2 Frontend — Inertia + React + Ant Design

| Kebutuhan | Package / Approach |
|-----------|-------------------|
| Bridge | Inertia.js v2 (React adapter) |
| UI Kit | Ant Design 5 (`antd`) |
| Tabel data | AntD **ProTable** (`@ant-design/pro-components`) — filter, sort, export |
| Charts | `@ant-design/charts` (G2Plot) atau `recharts` untuk tren |
| Form | AntD Form + `ProForm` (validasi, dependent fields) |
| State/data | Inertia props + `@tanstack/react-query` untuk polling real-time ringan |
| Build | Vite (default Laravel 11) |
| Icons | `@ant-design/icons` |
| Date | `dayjs` (bawaan AntD) |

### 7.3 Database — MySQL 8

- InnoDB, utf8mb4.
- Index komposit pada `(production_date, site_id)`, `(equipment_id)`, `(daily_entry_id)`.
- Pertimbangkan **generated columns** atau **summary tables** untuk agregat yang sering dibaca.
- Partitioning per tahun untuk `fuel_records`/`production_records` bila data historis besar.

#### 7.3.1 Integrasi API dengan arkfleet-next

ARKA MineOps mengambil data master equipment dari arkfleet-next murni via **REST API** (lihat §2.4) — bukan shared database, agar kedua aplikasi tetap bisa *deploy* & *scale* independen tanpa *coupling* skema DB.

| Kebutuhan | Package / Approach |
|-----------|---------------------|
| HTTP Client | Laravel HTTP Client (`Illuminate\Support\Facades\Http`) — memanggil endpoint arkfleet-next (§9.6) |
| Caching | Redis — cache hasil `GET /api/equipment` (per `project_code`/`plant_type`), TTL 1 jam |
| Resiliency | *Retry* otomatis (mis. `Http::retry(3, 100)`) + *circuit breaker* sederhana — kalau API arkfleet-next gagal berkali-kali, fallback ke data cache terakhir (*graceful degradation*, lihat §9.6) |
| Auth antar-app | Token API (Sanctum personal access token atau API key khusus service-to-service), dikirim via header `Authorization: Bearer` |
| Sinkronisasi cache | Webhook dari arkfleet-next saat equipment diubah, **atau** scheduled job (`schedule:run`) untuk refresh cache equipment secara periodik |

- Data equipment yang diambil dari API bersifat **read-only** di sisi MineOps — fleet lifecycle (tambah unit, ubah status, dsb.) tetap jadi tanggung jawab arkfleet-next.
- MineOps menyimpan **cached fields** (`equipment_id`, `unit_code`, `description`, `plant_type`) di tabel lokal (`fuel_records`, `equipment_deployments`) untuk menghindari API call berulang saat render dashboard/report — lihat §3.1 & §9.6.
- Kredensial/token API sebaiknya dibuat dengan scope terbatas (read-only pada endpoint equipment) agar MineOps tidak bisa mengubah data fleet.

#### 7.3.2 Integrasi API dengan ARK-GS (Procurement & Material KPI)

ARKA MineOps mengambil KPI procurement/material dari **ARK-GS** murni via **REST API** (lihat §2.5) — bukan sync SAP B1 sendiri, dan bukan shared database. Pola teknisnya **identik** dengan integrasi arkfleet-next (§7.3.1) agar konsisten dan mudah dirawat.

| Kebutuhan | Package / Approach |
|-----------|---------------------|
| HTTP Client | Laravel HTTP Client (`Illuminate\Support\Facades\Http`) via service `ProcurementApiService` — memanggil endpoint KPI ARK-GS (§9.7) |
| Caching | Redis — cache hasil per (`project_code`, `year`, `month`), **TTL ~6 jam** selaras jadwal sync ARK-GS (2x/hari 06:05 & 12:05 WITA) |
| Resiliency | `Http::retry(3, 100)` + `try/catch` → fallback ke cache terakhir (*graceful degradation*) + tampilkan warning & "last synced" timestamp (§9.7) |
| Auth antar-app | Token API (Sanctum PAT atau API key service-to-service), header `Authorization: Bearer` — scope read-only |
| Endpoint yang perlu di-*expose* ARK-GS | `GET /api/kpi/po-sent`, `GET /api/kpi/grpo`, `GET /api/kpi/npi`, `GET /api/kpi/budget` — semua menerima filter `project_code`, `year`, `month` |

- KPI procurement bersifat **read-only** di MineOps — sync & normalisasi SAP B1 tetap sepenuhnya tanggung jawab ARK-GS.
- Semua endpoint memakai `project_code` sebagai kunci join lintas sistem (konsisten dengan `sites.code` MineOps & `project_code` arkfleet-next), memungkinkan **Combined Operational View** (§4, §6).
- Respons API menyertakan `last_synced_at` agar MineOps bisa menampilkan kesegaran data ke user (§9.7).

### 7.4 Real-time & Offline

| Kebutuhan | Approach |
|-----------|----------|
| Real-time dashboard | Polling ringan via react-query (interval) — cukup untuk update harian. Upgrade ke Laravel Reverb (WebSocket) jika perlu live. |
| Offline (site sinyal jelek) | **PWA** (service worker) + IndexedDB untuk simpan draft entry lokal; sync queue saat online. |
| Mobile | Responsive AntD + PWA installable (add to home screen). Tidak perlu app native di fase awal. |

### 7.5 Deployment — Ubuntu VPS + Tailscale

- Nginx + PHP-FPM 8.3, MySQL 8, Redis.
- Tailscale untuk akses privat aman (dashboard tidak perlu expose publik; akses via tailnet).
- Supervisor/systemd untuk `queue:work` & Horizon.
- Deploy: Git + Deployer/`laravel envoy` atau GitHub Actions.
- Backup harian DB + file arsip Excel.

### 7.6 AI (opsional) — OpenRouter

> **Catatan:** ini adalah integrasi API yang **berbeda** dari integrasi equipment arkfleet-next (§7.3.1 & §9.6). OpenRouter adalah layanan AI pihak ketiga untuk fitur naratif/insight (Modul G), bukan bagian dari integrasi data equipment.

- Panggil via HTTP client Laravel (`Http::withToken(...)`).
- Use case: narrative summary harian, anomaly explanation, natural-language query → SQL/agregat.
- Simpan API key di `.env`, panggil dari queue job (jangan blok request).

---

## 8. Fase Implementasi

> Estimasi berbasis 1–2 developer. Anggap 1 "minggu" = 5 hari kerja.

### Fase 0 — Discovery & Setup (1 minggu)
- Finalisasi mapping kolom Excel (DPR 156 kolom, Fuel 39 sheet) → skema DB.
- Setup repo, Laravel 11 + Inertia + React + AntD, CI, VPS + Tailscale.
- **Deliverable:** skeleton app jalan + auth login.

### Fase 1 — Master Data & Foundation (1.5 minggu)
- Modul Master (Site/PIT multi-site, Shift, Fuel Type/Price).
- Setup integrasi **REST API** ke arkfleet-next (Laravel HTTP Client + cache Redis, lihat §7.3.1) + service `EquipmentApiService` + halaman **Equipment Assignment** (§4).
- Role & permission (4 role), termasuk akses per site.
- **Deliverable:** admin bisa kelola semua master data multi-site & assign equipment existing dari arkfleet-next (via API) ke PIT.

### Fase 2 — Daily Data Entry (2 minggu)
- Form Produksi, Fuel, Equipment Deployment, Info Site.
- Draft/Submit/Approve workflow.
- Calculation Engine (MTD/YTD/PTD, SR, FCR, Achievement).
- **Deliverable:** input harian penuh manual bekerja end-to-end.

### Fase 3 — Dashboard & Reporting (2 minggu)
- Executive dashboard + charts (OB/Coal/SR/Fuel trend).
- Fuel dashboard & equipment utilization.
- Auto-generate report PDF/Excel (template resmi).
- **Deliverable:** manajemen bisa lihat real-time + export report.

### Fase 4 — Plan vs Actual & Excel Import (2 minggu)
- Monthly plan input + achievement auto-calc + variance.
- Excel Import pipeline (DPR/Info/Fuel) dengan preview & validasi → **kunci migrasi data historis**.
- **Deliverable:** plan tracking + migrasi file lama.

### Fase 4B — Integrasi Procurement KPI (ARK-GS) (1.5 minggu)
- **Sisi ARK-GS:** kembangkan endpoint REST API baru: `GET /api/kpi/po-sent`, `GET /api/kpi/grpo`, `GET /api/kpi/npi`, `GET /api/kpi/budget` (filter `project_code`, `year`, `month`, sertakan `last_synced_at`) — konsisten dengan API controller ARK-GS yang sudah ada.
- **Sisi MineOps:** `ProcurementApiService` (Laravel HTTP Client + Redis cache TTL ~6 jam + fallback graceful degradation), Modul **MO-Procurement** (kartu Budget/GRPO/NPI/CAPEX), dan **Combined Operational View** (produksi + procurement, join via `project_code`).
- **Deliverable:** dashboard MineOps menampilkan KPI procurement/material dari SAP B1 (via ARK-GS) berdampingan dengan KPI produksi, lengkap dengan indikator "last synced".

### Fase 5 — Mobile/PWA & Offline (1.5 minggu)
- Responsive polish + PWA + offline draft & sync.
- **Deliverable:** supervisor input dari HP di site meski sinyal jelek.

### Fase 6 — Notification & AI (1.5 minggu)
- Alert achievement & fuel anomaly.
- Telegram daily summary bot.
- (Opsional) AI narrative & NL query via OpenRouter.
- **Deliverable:** notifikasi otomatis + insight.

### Fase 7 — UAT, Hardening & Rollout (1 minggu)
- User acceptance test dengan supervisor & fuel officer, **dilakukan di beberapa site** (mis. 022C dan minimal satu site lain) untuk memvalidasi desain multi-site & integrasi API sebelum rollout penuh.
- Import data historis batch, training user, cutover dari email.
- **Deliverable:** go-live.

**Total estimasi:** ± **13–15 minggu** (± 3–3,5 bulan) untuk versi lengkap (termasuk Fase 4B integrasi procurement ARK-GS). **MVP** (Fase 0–3) bisa dikejar dalam **±6–7 minggu**. Fase 4B bisa berjalan paralel dengan Fase 5 bila resource memungkinkan (dependensinya hanya endpoint API ARK-GS, bukan fitur produksi inti).

```mermaid
gantt
    title Roadmap ARKA MineOps
    dateFormat  YYYY-MM-DD
    axisFormat %b %d
    section Foundation
    F0 Discovery & Setup      :f0, 2026-08-01, 5d
    F1 Master Data            :f1, after f0, 8d
    section Core (MVP)
    F2 Daily Entry            :f2, after f1, 10d
    F3 Dashboard & Report     :f3, after f2, 10d
    section Extended
    F4 Plan & Excel Import    :f4, after f3, 10d
    F4B Procurement (ARK-GS)  :f4b, after f4, 8d
    F5 Mobile/PWA & Offline   :f5, after f4b, 8d
    F6 Notif & AI             :f6, after f5, 8d
    F7 UAT & Rollout          :f7, after f6, 5d
```

---

## 9. Pertimbangan Khusus

### 9.1 Handling Data Historis (ratusan file Excel existing)

**Tantangan:** Excel lama tidak konsisten (merged cells, formula, layout berubah antar bulan).

**Strategi:**
1. **Import bertahap, bukan sekaligus** — mulai dari 1-2 bulan terakhir untuk validasi mapping, baru mundur ke belakang.
2. **Mapping profile per template** — buat definisi mapping (kolom → field) yang bisa disesuaikan, karena layout bisa beda antar periode.
3. **Preview + human-in-the-loop** — hasil parse selalu ditampilkan untuk dikonfirmasi/koreksi sebelum commit ke DB. Flag baris yang mencurigakan (nilai kosong/anomali).
4. **Arsip file asli** — simpan Excel original (link ke `daily_entries.source_file`) untuk audit/traceability.
5. **Rekonsiliasi** — bandingkan agregat MTD hasil sistem vs angka MTD di Excel lama sebagai QA.
6. **Prioritas:** data agregat/summary lebih penting daripada setiap detail truck count historis. Bisa import summary dulu, detail belakangan (atau skip untuk periode sangat lama).

### 9.2 Strategi Migrasi dari Email → Aplikasi

**Pendekatan paralel (dual-run), bukan big-bang:**

```mermaid
flowchart LR
    A["Tahap 1<br/>Aplikasi jalan +<br/>email tetap"] --> B["Tahap 2<br/>Input di app,<br/>app auto-generate<br/>report utk email"]
    B --> C["Tahap 3<br/>Stakeholder biasa<br/>lihat dashboard,<br/>email dikurangi"]
    C --> D["Tahap 4<br/>Email dihentikan,<br/>full app"]
```

- **Tahap transisi kunci:** aplikasi meng-*generate* report berformat Excel/PDF yang **mirip file lama**, sehingga app bisa "menggantikan" pembuat email tanpa mengubah kebiasaan penerima dulu.
- **Change management:** training singkat per role, dampingi supervisor 1-2 minggu pertama.
- **Insentif:** tunjukkan bahwa input di app **lebih cepat** (tidak perlu rumus manual, auto-hitung MTD) — itu daya tarik adopsi.
- **Cutover** hanya setelah data konsisten & user nyaman.

### 9.3 Mobile-Friendly (supervisor pakai HP di site)

- **Responsive-first** untuk form entry; dashboard boleh dioptimalkan desktop tapi tetap terbaca di HP.
- **PWA installable** — "Add to Home Screen", buka seperti app.
- Input dioptimalkan: keyboard numerik, dropdown equipment dengan search, step-by-step wizard (bukan form panjang).
- Tombol besar, kontras tinggi (kondisi outdoor terik).
- Kompresi payload (data harian ringan) agar hemat kuota.

### 9.4 Offline Capability (sinyal jelek di site)

- **Service Worker + IndexedDB:** draft entry disimpan lokal, jalan tanpa internet.
- **Sync queue:** saat online, kirim antrian ke server; tangani konflik (last-write atau merge per field).
- **Indikator status jelas:** "📶 Offline — 2 entry belum sync" + tombol manual sync.
- **Idempotent submit:** pakai client-generated UUID agar submit ganda (akibat retry) tidak menduplikasi data.
- **Batasan realistis:** dashboard analitik butuh online; yang wajib offline adalah **data entry**, bukan lihat agregat.

### 9.5 Pertimbangan Lain

- **Konsistensi angka = nilai jual utama.** Selama ini tiap Excel bisa beda formula. Calculation Engine terpusat menjamin OB/Coal/SR/FCR dihitung dengan cara yang sama untuk semua.
- **Audit trail** — data operasional sering jadi dasar klaim/tagihan; catat siapa input/ubah apa.
- **Data ownership GPK vs ARKA** — bedakan Plan/Actual milik GPK dan ARKA (sudah tercermin di kolom `owner`).
- **Keamanan** — akses via Tailscale (privat), role-based, tidak expose ke publik di fase awal.
- **Skalabilitas** — desain sudah multi-site (`sites` table) sejak awal; ARKA MineOps ditujukan enterprise-wide untuk seluruh site PT. Arkananta (022C, 021C, 017C, 011C, dst.), bukan hanya satu site, memudahkan penambahan site baru nanti tanpa perubahan skema.

### 9.6 Integrasi Equipment via REST API

**Prinsip: clean separation of concerns, single source of truth untuk equipment, diakses murni via REST API — tidak ada shared database antara kedua aplikasi.**

#### 9.6.1 Endpoint yang Perlu Disediakan arkfleet-next

arkfleet-next perlu men-*expose* endpoint API berikut agar bisa dikonsumsi ARKA MineOps:

| Endpoint | Deskripsi |
|----------|-----------|
| `GET /api/equipment` | List equipment, dengan filter `project_code` (site), `plant_type`, `is_active` — dipakai untuk Equipment Assignment (§4) & search di form entry |
| `GET /api/equipment/{id}` | Detail satu equipment (unit_code, description, plant_type, status, dst.) |
| `GET /api/equipment/{id}/hm-km-readings` | History HM/KM readings equipment tersebut — dipakai untuk kalkulasi FCR (§3.1) |

#### 9.6.2 Arsitektur Konsumsi & Multi-Site

```mermaid
graph LR
    subgraph MASTER["MASTER — arkfleet-next"]
        M1["Fleet Management"]
        M2["Asset Tracking"]
        M3["Depreciation & Acquisition"]
        M4["HM/KM Readings"]
        API_EP["REST API<br/>/api/equipment · /api/equipment/{id}<br/>/api/equipment/{id}/hm-km-readings"]
    end

    subgraph OPS["OPERATIONAL — ARKA MineOps (multi-site)"]
        CACHE[("Redis Cache<br/>TTL 1 jam")]
        S1["Site 022C GPK"]
        S2["Site 021C SBI"]
        S3["Site 017C KPUC"]
        S4["Site 011C Kitadin, dst."]
    end

    M1 & M2 & M3 & M4 --> API_EP
    API_EP -->|"Laravel HTTP Client<br/>(request per project_code)"| CACHE
    CACHE --> S1 & S2 & S3 & S4
```

Beberapa site mengakses **data equipment yang sama** dari arkfleet-next melalui API yang sama, masing-masing memfilter dengan `project_code` sesuai site aktif (§2.4.2). Cache Redis dipakai bersama sehingga equipment yang sudah pernah di-fetch untuk satu site tidak perlu di-*request* ulang selama TTL belum habis.

| | arkfleet-next | ARKA MineOps |
|---|---------------|---------------|
| **Peran** | **Master** — fleet management, asset tracking, depreciation, HM/KM readings | **Operational** — daily production, fuel, deployment per shift, multi-site |
| **Kepemilikan data equipment** | Sumber kebenaran tunggal (single source of truth) | Hanya mereferensikan via `equipment_id` + cached fields, tidak mengakses tabel arkfleet-next langsung |
| **CRUD equipment** | Ya — tambah/ubah/hapus unit, ubah status, dsb. | Tidak — read-only via API, hanya assign ke PIT |
| **Cara akses** | — | Laravel HTTP Client → REST API → cache Redis (TTL 1 jam) |

#### 9.6.3 Caching Strategy

- Equipment list & detail di-**cache di Redis** (TTL 1 jam) per kombinasi filter (`project_code`, `plant_type`, `is_active`) agar tidak membebani arkfleet-next dengan request berulang.
- **Invalidasi cache** dilakukan via:
  1. **Webhook** dari arkfleet-next saat equipment ditambah/diubah/dihapus — MineOps langsung refresh cache terkait, atau
  2. **Scheduled refresh** (Laravel Scheduler) yang menarik ulang data equipment secara periodik sebagai *safety net* jika webhook gagal terkirim.

#### 9.6.4 Fallback — Graceful Degradation

- Jika API arkfleet-next **down atau timeout**, MineOps **tidak boleh gagal total** — tampilkan data dari cache terakhir yang masih tersimpan (walau sudah lewat TTL) dan tampilkan **warning** ke user (mis. "Data equipment mungkin tidak terkini, koneksi ke arkfleet-next terganggu").
- Fitur yang butuh data *real-time* equipment (mis. Equipment Assignment untuk unit baru) akan menunjukkan pesan error yang jelas, tapi fitur yang hanya butuh data cache (dashboard, fuel entry ke equipment yang sudah pernah diakses) tetap bisa berjalan.
- Implementasi teknis: `Http::retry(3, 100)` + `try/catch` di service layer, fallback ke `Cache::get()` tanpa melempar exception ke user.

#### 9.6.5 Equipment Selection & Local Cache

Alur saat user memilih equipment di MineOps (Equipment Assignment maupun form entry):

1. User **search** equipment dari API (`GET /api/equipment?project_code=...&search=...`), hasil diambil dari cache Redis bila tersedia.
2. User **pilih** unit yang relevan.
3. MineOps menyimpan `equipment_id` **beserta cached fields** (`unit_code`, `description`, `plant_type`) di tabel lokal (`fuel_records`, `equipment_deployments`) — sehingga render dashboard/report berikutnya **tidak perlu API call ulang**, cukup baca kolom lokal.

**Manfaat pendekatan ini:**
- **No data duplication di source-of-truth** — unit_code, model, status, dst. tetap hanya dikelola satu tempat (arkfleet-next); MineOps hanya menyimpan *cached copy* untuk performa, bukan sebagai sumber kebenaran baru.
- **Decoupled & independen** — ARKA MineOps dan arkfleet-next bisa di-*deploy*/*scale* terpisah, tanpa *coupling* skema database (lihat §2.4.1).
- **Fleet lifecycle** (akuisisi, penjualan, scrap) tetap sepenuhnya dikelola tim fleet di arkfleet-next — MineOps tidak perlu tahu/terlibat proses ini, cukup filter equipment yang `is_active`/`unitstatus = ACTIVE` saat assignment.
- **Skala multi-site** — pendekatan API + cache yang sama berlaku untuk semua site (022C, 021C, 017C, 011C, dst.), tidak perlu integrasi khusus per site.

### 9.7 Integrasi Procurement via REST API (ARK-GS)

**Prinsip: sama seperti integrasi equipment (§9.6) — single source of truth untuk data SAP B1 ada di ARK-GS, MineOps hanya mengonsumsi KPI via REST API + cache, tanpa shared database dan tanpa menduplikasi sync SAP.**

#### 9.7.1 Endpoint yang Perlu Disediakan ARK-GS

| Endpoint | Deskripsi |
|----------|-----------|
| `GET /api/kpi/po-sent` | PO Sent per project (agregat `item_amount`) + budget periode → % budget terserap. Filter `project_code`, `year`, `month`. |
| `GET /api/kpi/grpo` | GRPO vs PO Sent per project → % completion + status (Good/Attention/Critical). Filter `project_code`, `year`, `month`. |
| `GET /api/kpi/npi` | Incoming vs Outgoing per project → NPI index. Filter `project_code`, `year`, `month`. |
| `GET /api/kpi/budget` | Budget (regular + CAPEX) vs actual per project. Filter `project_code`, `year`, `month`. |

Semua respons menyertakan `last_synced_at` (waktu sync SAP B1 terakhir oleh ARK-GS).

#### 9.7.2 Data Freshness — Bergantung Jadwal Sync ARK-GS

- ARK-GS men-sync SAP B1 **dua kali sehari (06:05 & 12:05 WITA)**. Artinya KPI procurement **tidak real-time** seperti data produksi — paling segar sekitar jadwal sync tersebut.
- TTL cache Redis di MineOps di-set **selaras** (mis. ~6 jam) agar tidak memanggil ARK-GS berlebihan tapi tetap menangkap hasil sync terbaru.
- **Selalu tampilkan timestamp "last synced"** di setiap kartu/halaman procurement (§6) agar user paham kesegaran data dan tidak salah menafsirkan angka lama sebagai real-time.

#### 9.7.3 Fallback — Graceful Degradation

- Jika ARK-GS API **down atau timeout**, MineOps **tidak boleh gagal total**: tampilkan data cache terakhir (walau lewat TTL) + warning (mis. "Data procurement mungkin tidak terkini, koneksi ke ARK-GS terganggu — last synced: …").
- KPI produksi (native) tetap tampil normal meski procurement gagal di-fetch — dashboard degradasi sebagian, bukan seluruhnya.
- Implementasi teknis: `Http::retry(3, 100)` + `try/catch` di `ProcurementApiService`, fallback ke `Cache::get()` tanpa melempar exception ke user (sama seperti pola §9.6.4).

#### 9.7.4 Konsistensi Kunci `project_code`

- `project_code` di ARK-GS (`017C`, `021C`, `022C`, `025C`, `026C`, `APS`, `023C`) harus dipetakan ke `sites.code` MineOps. Kode yang sama persis (mis. `022C`) langsung join; kode yang hanya ada di salah satu sistem ditandai agar tidak "hilang" di combined view.
- Rekomendasi: buat tabel/konfigurasi pemetaan `project_code ↔ site_id` bila ada perbedaan penamaan, agar Combined Operational View akurat lintas produksi + procurement.

---

## Ringkasan Eksekutif

**ARKA MineOps** mengubah 3 laporan Excel terpisah (DPR, Daily Info Site, Fuel Report) yang dikirim manual via email menjadi **satu dashboard terintegrasi real-time**, dirancang **enterprise-wide untuk seluruh site PT. Arkananta** (022C GPK, 021C SBI, 017C KPUC, 011C Kitadin, dan site lain) — bukan hanya satu site tunggal. Kuncinya: menyatukan ketiga laporan pada poros **waktu (tanggal+shift)** dan **aset (equipment+PIT)**, dengan **Calculation Engine terpusat** yang menjamin angka MTD/YTD/SR/FCR/Achievement selalu konsisten di semua site, dan **site selection sebagai first-class citizen** di UI (site selector di navbar/dashboard).

Penting: ARKA MineOps **tidak membangun ulang master data equipment**. Aplikasi ini memanfaatkan registry equipment yang sudah ada di **arkfleet-next** (± 1.000 unit lintas site, kode unit persis sama dengan Excel lama, termasuk HM/KM readings) melalui **REST API** — bukan shared database, agar kedua aplikasi tetap *decoupled*, bisa di-*deploy* & di-*scale* independen, tanpa *coupling* skema. MineOps mereferensikan `equipment_id` dan menyimpan *cached copy* field yang sering diakses (dengan caching layer Redis, TTL 1 jam, plus fallback *graceful degradation*) untuk performa, sambil menjaga single source of truth untuk data alat berat tetap di arkfleet-next.

Sama pentingnya: ARKA MineOps **tidak menduplikasi sync SAP B1**. KPI **procurement & material** — **PO Sent vs Budget, GRPO completion, NPI efficiency, dan Budget/CAPEX vs actual** — sudah di-sync dari SAP B1 (2x/hari) oleh aplikasi existing **ARK-GS**, dan MineOps **mengonsumsinya via REST API** dengan pola yang sama (HTTP Client + Redis cache + fallback + indikator "last synced"). Hasilnya, MineOps menjadi **unified dashboard tiga sistem**: data **produksi** (native) + **equipment** (arkfleet-next) + **procurement/material** (ARK-GS ← SAP B1), disatukan lewat kunci `project_code`/site — sehingga manajemen bisa melihat KPI operasional dan pengadaan dalam **satu layar (Combined Operational View)**.

Pendekatan implementasi: **MVP 6-7 minggu** (master data multi-site + entry + dashboard), lalu extend ke plan tracking, Excel import (migrasi), PWA offline, dan notifikasi/AI. Testing dilakukan di beberapa site untuk memvalidasi desain multi-site. Migrasi dilakukan **paralel (dual-run)** agar transisi dari kebiasaan email berjalan mulus.

*Dokumen ini adalah konsep awal untuk didiskusikan sebelum masuk fase teknis/coding.*
