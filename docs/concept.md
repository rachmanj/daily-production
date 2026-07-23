# ARKA MineOps — Konsep Aplikasi Integrasi Laporan Operasional Tambang

> **Dokumen Konsep (Brainstorming)** · Greenfield Project
> PT. Arkananta · Site #022C Graha Panca Karsa Coal Project, Melak, Kalimantan Timur
> Versi 0.1 · Status: Draft konsep (belum coding)

---

## 1. Nama Aplikasi

**ARKA MineOps** — *Integrated Mining Operations Dashboard*

Alternatif nama (jika perlu opsi):

| Nama | Tagline | Kesan |
|------|---------|-------|
| **ARKA MineOps** ✅ | "One Site, One Truth" | Profesional, langsung ke inti (mining operations) |
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
        TG["Telegram / WhatsApp"]
        AI["OpenRouter API<br/>(opsional: anomaly & insight)"]
    end

    WEB & MOBILE --> HTTP
    HTTP --> SVC --> CALC
    HTTP --> IMPORT & EXPORT
    SVC --> MYSQL
    CALC --> REDIS
    IMPORT --> FILES & MYSQL
    NOTIF --> TG
    SVC -.opsional.-> AI
    NOTIF --> REDIS
```

**Keputusan arsitektur kunci:**

1. **Calculation Engine terpusat** — MTD/YTD/PTD, Stripping Ratio, FCR, dan Achievement % dihitung di backend secara konsisten (tidak lagi tersebar di formula Excel yang rawan salah). Data mentah harian disimpan, agregat dihitung/di-cache.
2. **Import Pipeline berbasis Queue** — parsing Excel besar (778×156) jalan di background job agar UI tidak nge-hang.
3. **Data mentah vs. agregat dipisah** — tabel harian menyimpan angka aktual per hari; kolom MTD/YTD di Excel lama TIDAK disimpan mentah, melainkan diturunkan (derived) agar selalu konsisten.

---

## 3. Entity Relationship Diagram (ERD)

Model data dirancang agar ketiga laporan menyatu pada `equipment`, `site/pit`, `shift`, dan `production_date`.

```mermaid
erDiagram
    USERS ||--o{ DAILY_ENTRIES : creates
    ROLES ||--o{ USERS : has

    SITES ||--o{ PITS : contains
    PITS ||--o{ PRODUCTION_RECORDS : "produced at"

    EQUIPMENT_TYPES ||--o{ EQUIPMENT : classifies
    EQUIPMENT ||--o{ FUEL_RECORDS : consumes
    EQUIPMENT ||--o{ EQUIPMENT_DEPLOYMENTS : "deployed as"
    EQUIPMENT ||--o{ EQUIPMENT_READINGS : "logs HM/KM"

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
        string code "022C"
        string name "Graha Panca Karsa"
        string location "Melak, Kaltim"
    }
    PITS {
        bigint id PK
        bigint site_id FK
        string code "PIT1/PIT2"
        string owner "GPK/ARKA"
    }
    EQUIPMENT_TYPES {
        bigint id PK
        string category "Excavator/Dozer/ADT/Dumptruck/Support"
        string role "Loader/Hauler/Support"
    }
    EQUIPMENT {
        bigint id PK
        bigint equipment_type_id FK
        string unit_code "E 071"
        string model "Hitachi EX1200-6"
        string asset_no
        string ownership "ARKA/GPK/Rental"
        string status "active/breakdown/standby"
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
        bigint equipment_id FK
        bigint shift_id FK
        bigint fuel_type_id FK
        decimal liters
        decimal working_hours
        string usage_category "WasteLoading/WasteHauling/Dewatering/General"
    }
    EQUIPMENT_READINGS {
        bigint id PK
        bigint equipment_id FK
        date reading_date
        decimal hm_km_start
        decimal hm_km_end
    }
    EQUIPMENT_DEPLOYMENTS {
        bigint id PK
        bigint daily_entry_id FK
        bigint equipment_id FK
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
```

### 3.1 Catatan Desain Tabel

- **`daily_entries` sebagai "header" harian** — satu record per (tanggal, site). Semua detail (produksi, fuel, deployment, info site) tergantung padanya. Ini yang menggantikan "satu file Excel per hari".
- **Agregat (MTD/YTD/PTD/Achievement) TIDAK disimpan sebagai kolom mentah** — dihitung on-the-fly oleh Calculation Engine + di-cache di Redis. Alternatif untuk performa: tabel `production_aggregates` (materialized summary) yang di-refresh saat data harian di-approve.
- **`equipment_readings` terpisah dari `fuel_records`** — karena HM/KM awal-akhir adalah properti alat per hari, bukan per pemakaian fuel. Menghindari duplikasi.
- **Stripping Ratio** = `Σ OB (bcm) / Σ Coal (ton)` — metrik turunan, tidak disimpan mentah.
- **FCR (Fuel Consumption Ratio)** = liter fuel per satuan produksi (bcm/ton) atau per jam kerja — turunan dari `fuel_records` + `production_records`.

---

## 4. Daftar Modul / Fitur

### Modul A — Master Data Management (`MO-Master`)
| Fitur | Deskripsi |
|-------|-----------|
| Equipment Registry | CRUD 40+ unit: unit code, tipe, model, asset no, ownership, status. Tracking HM/KM. |
| Site & PIT Config | Kelola site (022C) dan PIT (PIT1 GPK, PIT2 GPK), kepemilikan area. |
| Shift Definition | Day/Night dengan jam mulai-selesai. |
| Fuel Type & Price | Jenis solar + histori harga per tanggal efektif (untuk kalkulasi biaya). |
| User & Role | Manajemen user + role-based access. |

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
│  ARKA MineOps      Site: 022C ▼   📅 31 Mei 2026 ▼      🔔 3   👤 Budi │
├────────────┬─────────────────────────────────────────────────────────┤
│ ▤ Dashboard│  RINGKASAN HARI INI                    [Export ▼] [Report]│
│ ✎ Entry    │ ┌───────────┐┌───────────┐┌───────────┐┌───────────┐    │
│ ⛏ Produksi │ │ OB REMOVAL││COAL GETTING││ STRIPPING ││   FUEL    │    │
│ ⛽ Fuel     │ │ 45.230 Bcm││ 8.120 ton ││  RATIO    ││ 32.400 L  │    │
│ 🚜 Equipment│ │ ▲ 96% plan││ ▲ 92% plan││   5.57    ││ FCR 0.71  │    │
│ 📋 Plan     │ └───────────┘└───────────┘└───────────┘└───────────┘    │
│ 📊 Reports  │  ┌─────────────────────────┐┌──────────────────────┐   │
│ ⚙ Master    │  │ TREN OB & COAL (30 hari)││ ACHIEVEMENT MTD      │   │
│ 👥 Users    │  │   ╱╲    ╱╲___╱          ││  OB   ███████░░ 88%  │   │
│             │  │  ╱  ╲__╱                ││  Coal ██████░░░ 79%  │   │
│             │  │ (line chart)            ││  (gauge/progress)    │   │
│             │  └─────────────────────────┘└──────────────────────┘   │
│             │  ┌─────────────────────────┐┌──────────────────────┐   │
│             │  │ PRODUKSI PER PIT        ││ STATUS ALAT          │   │
│             │  │ PIT1 GPK  ██████ 24.1k  ││ ● Active     34      │   │
│             │  │ PIT2 GPK  ████   21.1k  ││ ● Breakdown   4      │   │
│             │  │ (bar chart)             ││ ● Standby     2      │   │
│             │  └─────────────────────────┘└──────────────────────┘   │
└────────────┴─────────────────────────────────────────────────────────┘
```

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
- Index komposit pada `(production_date, site_id)`, `(equipment_id, reading_date)`, `(daily_entry_id)`.
- Pertimbangkan **generated columns** atau **summary tables** untuk agregat yang sering dibaca.
- Partitioning per tahun untuk `fuel_records`/`production_records` bila data historis besar.

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
- Modul Master (Equipment, Site/PIT, Shift, Fuel Type/Price).
- Role & permission (4 role).
- Seed 40+ equipment dari daftar existing.
- **Deliverable:** admin bisa kelola semua master data.

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

### Fase 5 — Mobile/PWA & Offline (1.5 minggu)
- Responsive polish + PWA + offline draft & sync.
- **Deliverable:** supervisor input dari HP di site meski sinyal jelek.

### Fase 6 — Notification & AI (1.5 minggu)
- Alert achievement & fuel anomaly.
- Telegram daily summary bot.
- (Opsional) AI narrative & NL query via OpenRouter.
- **Deliverable:** notifikasi otomatis + insight.

### Fase 7 — UAT, Hardening & Rollout (1 minggu)
- User acceptance test dengan supervisor & fuel officer.
- Import data historis batch, training user, cutover dari email.
- **Deliverable:** go-live.

**Total estimasi:** ± **12–13 minggu** (± 3 bulan) untuk versi lengkap. **MVP** (Fase 0–3) bisa dikejar dalam **±6–7 minggu**.

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
    F5 Mobile/PWA & Offline   :f5, after f4, 8d
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
- **Skalabilitas** — desain sudah multi-site (`sites` table) walau sekarang hanya 022C, memudahkan ekspansi ke site lain nanti.

---

## Ringkasan Eksekutif

**ARKA MineOps** mengubah 3 laporan Excel terpisah (DPR, Daily Info Site, Fuel Report) yang dikirim manual via email menjadi **satu dashboard terintegrasi real-time**. Kuncinya: menyatukan ketiga laporan pada poros **waktu (tanggal+shift)** dan **aset (equipment+PIT)**, dengan **Calculation Engine terpusat** yang menjamin angka MTD/YTD/SR/FCR/Achievement selalu konsisten.

Pendekatan implementasi: **MVP 6-7 minggu** (master data + entry + dashboard), lalu extend ke plan tracking, Excel import (migrasi), PWA offline, dan notifikasi/AI. Migrasi dilakukan **paralel (dual-run)** agar transisi dari kebiasaan email berjalan mulus.

*Dokumen ini adalah konsep awal untuk didiskusikan sebelum masuk fase teknis/coding.*
