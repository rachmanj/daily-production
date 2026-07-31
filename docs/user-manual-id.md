# Panduan Pengguna ARKA MineOps

> **Versi:** 1.2 · **Terakhir diperbarui:** 31 Juli 2026  
> PT. Arkananta · Integrated Mining Operations Dashboard

---

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Memulai](#2-memulai)
3. [Modul Aplikasi](#3-modul-aplikasi)
4. [Master Data (Admin)](#4-master-data-admin)
5. [Penggunaan Mobile](#5-penggunaan-mobile)
6. [Troubleshooting](#6-troubleshooting)

---

## 1. Pendahuluan

### 1.1 Apa itu ARKA MineOps?

**ARKA MineOps** (*Integrated Mining Operations Dashboard*) adalah platform web terintegrasi untuk mengelola operasional tambang PT. Arkananta. Aplikasi ini menggantikan siklus laporan manual **Excel → Email → Download → Merge** dengan satu sumber kebenaran (*single source of truth*) yang real-time.

Filosofi produk: **"Input sekali, dilihat semua, real-time."**

ARKA MineOps menyatukan tiga laporan harian legacy:

| Laporan Legacy | Modul MineOps |
|----------------|---------------|
| DPR (Daily Production Report) | Tab **Produksi** |
| Fuel Report | Tab **Fuel** |
| Daily Info Site | Tab **Info Site** + **Deployment** |

Selain produksi harian, platform ini juga mencakup monitoring fuel, plan vs actual, procurement KPI (dari SAP B1 via ARK-GS), CCR hourly production (021C/025C), CCR 022C trip production, dan laporan otomatis PDF/Excel.

### 1.2 Ringkasan Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| **Executive Dashboard** | KPI produksi (OB, Coal, SR, Fuel), trend chart, status alat, produksi per-PIT |
| **Daily Entry** | Input harian terpadu: produksi, fuel, deployment, info site; tab total CCR hourly di site CCR-enabled |
| **Fuel Dashboard** | Monitoring konsumsi BBM per alat, FCR, trend |
| **CCR Hourly** | Produksi per jam per alat (Limestone, Shalestone) di 021C/025C — dashboard KPI, heatmap, grid input; terintegrasi ke Daily Entry |
| **CCR 022C** | Produksi trip-level (OB, Coal, Top Soil) di 022C — import Excel, entry manual, pairing excavator×hauler, rekonsiliasi |
| **Plan vs Actual** | Target bulanan per PIT, achievement %, analisis variance |
| **Procurement KPI** | Budget, GRPO, NPI dari ARK-GS (SAP B1) |
| **Reports** | Laporan harian, custom periode, konsolidasi multi-site |
| **Notifikasi** | Alert achievement, anomali fuel, reminder input |
| **Multi-site** | Enterprise-wide: 022C, 021C, 017C, 011C, 025C, 026C, 023C, APS |
| **PWA & Offline** | Instalasi di HP, input draft offline, sinkronisasi otomatis |

### 1.3 Akses & Kredensial

| Item | Nilai |
|------|-------|
| **URL Aplikasi** | https://vps-iwan1.tail8334ce.ts.net |
| **Email demo** | `admin@mineops.test` |
| **Password demo** | `password` |
| **Login alternatif** | Username (jika diset admin) |
| **Zona waktu** | WITA (Asia/Makassar) |

> **Catatan keamanan:** Ganti password default segera setelah login pertama. Akun produksi dikelola oleh Admin melalui **Sidebar → Pengguna**.

### 1.4 Peran Pengguna

| Peran | Hak Akses Utama |
|-------|-----------------|
| **Admin** | Akses penuh: master data, pengguna, equipment assignment, semua modul |
| **Supervisor** | Input & approve daily entry, dashboard, laporan |
| **Management** | Dashboard, kelola plan bulanan, laporan, variance |
| **Fuel Officer** | Input daily entry (khusus fuel), dashboard fuel |

---

## 2. Memulai

### 2.1 Proses Login

1. Buka browser (Chrome, Edge, atau Safari direkomendasikan).
2. Akses URL: **https://vps-iwan1.tail8334ce.ts.net**
3. Anda akan diarahkan ke halaman login.

**Tampilan halaman login:**
- Layout *split-screen*: panel kiri berwarna gradien dengan branding ARKA MineOps, panel kanan berisi form login.
- Judul: **ARKA MineOps** — *Sistem Manajemen Operasional Tambang*
- Form berisi:
  - **Email atau Username** — masukkan alamat email atau username
  - **Password** — masukkan kata sandi
  - **Ingat saya** — centang untuk sesi persisten
  - Tombol **Masuk**

4. Klik **Masuk**. Jika berhasil, Anda diarahkan ke **Executive Dashboard**.
5. Jika gagal, pesan error ditampilkan di bawah field yang bermasalah.

> **Screenshot:** Halaman login menampilkan kartu form putih di atas background gradien biru gelap dengan logo ⛏️ ARKA MineOps.

### 2.2 Ikhtisar Dashboard

Setelah login, halaman utama adalah **Executive Dashboard** (`Sidebar → Dashboard`).

Komponen utama:

| Area | Fungsi |
|------|--------|
| **Filter atas** | Pilih site, tanggal, tombol **Terapkan** |
| **KPI Cards** | OB Removal, Coal Getting, Stripping Ratio (MTD), Fuel Today |
| **Trend Chart** | Grafik tren produksi harian |
| **Equipment Status** | Ringkasan alat: aktif, standby, breakdown |
| **Per-PIT Chart** | Produksi breakdown per PIT |
| **Drill-down** | Detail per-PIT dalam drawer |

Data dashboard di-refresh otomatis setiap 60 detik.

### 2.3 Site Selector (Multi-site)

ARKA MineOps dirancang **enterprise-wide** — setiap pengguna dapat beralih antar site operasional.

**Lokasi:** Header kanan atas (desktop) atau menu **⋯** (mobile).

**Cara menggunakan:**
1. Klik dropdown **Site Selector** di header.
2. Pilih site, misalnya `022C — GPK Project`.
3. Site aktif tersimpan di sesi Anda dan mempengaruhi:
   - Data dashboard
   - Daftar daily entry
   - Equipment assignment
   - Filter laporan

**Daftar site operasional:**

| Kode | Nama |
|------|------|
| 022C | GPK Project |
| 021C | SBI Project |
| 017C | KPUC Project |
| 011C | Kitadin Project |
| 025C | SBI Project 2 |
| 026C | CEP Project |
| 023C | Bharinto Project |
| APS | Arka Project Support |

### 2.4 Navigasi Sidebar

Menu utama di sidebar kiri (desktop) atau drawer (mobile):

| Menu | Path | Deskripsi |
|------|------|-----------|
| **Dashboard** | `Sidebar → Dashboard` | Executive Dashboard KPI |
| **Daily Entry** | `Sidebar → Daily Entry` | Input & kelola entry harian |
| **CCR Hourly** | `Sidebar → CCR Hourly` | Submenu: Hourly Entry, CCR Dashboard, Import CCR 022C, Trip Entry 022C |
| **Fuel** | `Sidebar → Fuel` | Dashboard konsumsi BBM |
| **Equipment** | `Sidebar → Equipment` | Penugasan alat ke PIT *(Admin)* |
| **Sites** | `Sidebar → Sites` | Master data site *(Admin)* |
| **PITs** | `Sidebar → PITs` | Master data PIT *(Admin)* |
| **Shifts** | `Sidebar → Shifts` | Master data shift *(Admin)* |
| **Jenis BBM** | `Sidebar → Jenis BBM` | Tipe bahan bakar *(Admin)* |
| **Harga BBM** | `Sidebar → Harga BBM` | Harga per jenis BBM *(Admin)* |
| **Pengguna** | `Sidebar → Pengguna` | Manajemen user & role *(Admin)* |
| **Monthly Plan** | `Sidebar → Monthly Plan` | Target produksi bulanan |
| **Variance** | `Sidebar → Variance` | Analisis variance plan vs actual |
| **Procurement** | `Sidebar → Procurement` | KPI pengadaan dari SAP B1 |
| **Reports** | `Sidebar → Reports` | Generate & download laporan |
| **Notifikasi** | `Sidebar → Notifikasi` | Daftar notifikasi sistem |

**Header kanan atas (desktop):**
- Tombol **Sync** — sinkronisasi data offline
- **Site Selector**
- **Bell icon** — notifikasi belum dibaca
- **Theme Toggle** — mode gelap/terang
- **Avatar** — profil & logout

### 2.5 Instalasi PWA (Mobile)

ARKA MineOps mendukung instalasi sebagai Progressive Web App (PWA) untuk akses seperti aplikasi native di smartphone.

**Android (Chrome):**
1. Buka URL aplikasi di Chrome.
2. Ketuk menu **⋮** → **Install app** / **Add to Home screen**.
3. Konfirmasi instalasi.
4. Ikon ARKA MineOps muncul di home screen.

**iOS (Safari):**
1. Buka URL di Safari.
2. Ketuk tombol **Share** (kotak dengan panah).
3. Pilih **Add to Home Screen**.
4. Beri nama dan tap **Add**.

---

## 3. Modul Aplikasi

### 3.1 Dashboard

**Path:** `Sidebar → Dashboard`

#### Executive Dashboard

Menampilkan ringkasan KPI operasional untuk site dan tanggal yang dipilih.

**KPI Cards:**

| KPI | Unit | Keterangan |
|-----|------|------------|
| **OB Removal** | Bcm | Overburden removal hari ini + MTD + achievement % |
| **Coal Getting** | Ton | Produksi batubara hari ini + MTD + achievement % |
| **Stripping Ratio (MTD)** | — | Rasio OB/Coal bulan berjalan |
| **Fuel Today** | Liter | Total konsumsi BBM hari ini + MTD |

**Trend Charts:**
- Grafik garis menampilkan tren produksi harian dalam periode terpilih.
- Hover pada titik data untuk melihat nilai detail.

**Per-PIT Production:**
- Chart batang produksi per PIT (OB dan Coal).
- Klik **Drill-down** untuk membuka drawer detail per-PIT.

**Equipment Status:**
- Ringkasan status armada: **Aktif**, **Standby**, **Breakdown**, dan total unit.
- Data bersumber dari equipment deployment harian.

**Filter:**
1. Pilih **Site** dari dropdown.
2. Pilih **Tanggal** via DatePicker.
3. Klik **Terapkan**.

---

### 3.2 Daily Entry

**Path:** `Sidebar → Daily Entry`

Modul inti untuk input data operasional harian — menggantikan tiga file Excel terpisah.

#### Membuat Daily Entry Baru

1. Buka `Sidebar → Daily Entry`.
2. Klik tombol **Entry Baru** (+).
3. Isi:
   - **Tanggal Produksi** — tanggal operasional
   - **Site** — site operasional
4. Klik **Buat Entry**.
5. Anda diarahkan ke halaman edit dengan empat tab (ditambah tab **CCR Hourly** di site CCR-enabled — lihat di bawah).

> Satu entry unik per kombinasi **tanggal + site**. Jika sudah ada, sistem menolak duplikasi.

#### Tab Produksi

**Path:** `Daily Entry → [Entry] → Tab Produksi`

Input data produksi per PIT dan shift.

| Field | Keterangan |
|-------|------------|
| **PIT** | PIT operasional |
| **Shift** | Siang / Malam |
| **Aktivitas** | OB, Coal, Top Soil, MUD, High Ash Coal |
| **Volume** | Jumlah produksi (Bcm untuk OB, Ton untuk Coal) |

**Langkah:**
1. Tambah baris produksi (+).
2. Isi PIT, shift, aktivitas, dan volume.
3. Klik **Simpan Produksi**.

#### Tab Fuel

**Path:** `Daily Entry → [Entry] → Tab Fuel`

Input pemakaian solar per alat berat.

| Field | Keterangan |
|-------|------------|
| **Unit** | Kode alat (dari equipment assignment) |
| **Shift** | Siang / Malam |
| **Kategori** | Waste Loading, Waste Hauling, Dewatering, General & Support |
| **Jenis BBM** | Tipe solar (dari master data) |
| **Liter** | Jumlah konsumsi (liter) |
| **Jam Kerja** | Jam operasional alat |

**Langkah:**
1. Tambah baris fuel.
2. Pilih unit dari daftar alat yang ter-assign di site aktif.
3. Isi kategori, jenis BBM, liter, dan jam kerja.
4. Klik **Simpan Fuel**.

#### Tab Deployment

**Path:** `Daily Entry → [Entry] → Tab Deployment`

Input penempatan alat per PIT dan shift (menggantikan Daily Info Site — bagian equipment).

| Field | Keterangan |
|-------|------------|
| **Unit** | Kode alat |
| **PIT** | Lokasi penugasan |
| **Shift** | Siang / Malam |
| **Status** | Aktif / Standby / Breakdown |
| **RFU** | Ready For Use (ya/tidak) |

**Langkah:**
1. Tambah baris deployment.
2. Isi unit, PIT, shift, dan status.
3. Klik **Simpan Deployment**.

#### Tab Info Site

**Path:** `Daily Entry → [Entry] → Tab Info Site`

Input informasi umum site harian (menggantikan Daily Info Site — bagian non-equipment).

| Field | Keterangan |
|-------|------------|
| **Cuaca** | Cerah / Hujan / Berawan |
| **Jam Hujan** | Durasi hujan (jam) |
| **Jam Licin** | Durasi kondisi licin (jam) |
| **Manpower Plan** | Rencana tenaga kerja |
| **Manpower Aktual** | Tenaga kerja aktual |
| **Stok BBM (Liter)** | Stok solar di site |
| **Catatan Keselamatan** | Catatan K3 |

**Langkah:**
1. Isi semua field relevan.
2. Klik **Simpan Info Site**.

#### Tab CCR Hourly *(hanya site CCR-enabled)*

**Path:** `Daily Entry → [Entry] → Tab CCR Hourly`

Di site yang mengaktifkan CCR (021C, 025C, 017C, 022C), tab tambahan ini menampilkan **total harian live** yang diagregasi dari data hourly production untuk entry tersebut. Di site **022C**, total dapat berasal dari rollup data trip (lihat §3.7).

| Kolom | Keterangan |
|-------|------------|
| **Material** | Jenis material (mis. Limestone, Coal, Overburden) |
| **Total (Mton)** | Jumlah tonase semua jam pada hari itu |
| **Jam Terisi** | Jumlah slot jam yang sudah diisi (dari 24) |
| **Plan DTD** | Target harian dari material plan (jika dikonfigurasi) |
| **Achievement** | Actual vs plan % (badge berwarna) |

**Badge di header:** Jika ada data hourly, header entry juga menampilkan tag ringkas seperti `Coal: 1.240 ton` per material.

**Penting:**
- Total langsung terupdate saat Anda mengisi grid Hourly Entry — bahkan saat daily entry masih **Draft**.
- Tab ini **read-only**; tidak menulis ke tab Produksi (OB/Coal). KPI SR/FCR dan executive dashboard tidak berubah.
- Klik **Buka Hourly Entry** untuk membuka grid input hourly pada tanggal dan site yang sama.

> Jika tab menampilkan "Belum ada data hourly untuk entry ini", buat atau edit data hourly via `Sidebar → CCR Hourly → Hourly Entry`.

#### Workflow: Draft → Submit → Approve

Setiap daily entry mengikuti alur persetujuan:

```
Draft → Submit → Approve
              ↘ Reject → kembali ke Draft
```

| Status | Label | Keterangan |
|--------|-------|------------|
| `draft` | Draf | Masih bisa diedit |
| `submitted` | Disubmit | Menunggu persetujuan |
| `approved` | Disetujui | Final; cache KPI dihitung ulang |

**Langkah workflow:**

1. **Draft** — lengkapi semua tab, simpan per tab.
2. Buka halaman detail entry (`Daily Entry → [Entry] → Lihat`).
3. Klik **Submit** — status berubah ke *Disubmit*, entry tidak bisa diedit.
4. **Approver** (Supervisor/Admin) klik **Approve** atau **Reject**.
5. Setelah **Approve**, data masuk perhitungan MTD/YTD/SR/FCR.

> Setiap tab disimpan terpisah via tombol **Simpan** masing-masing. Pastikan semua tab tersimpan sebelum Submit.

#### Excel Import

**Path:** `Sidebar → Daily Entry → Import Excel` (tombol Upload)

Alternatif input massal dari file Excel legacy.

**Langkah:**
1. Buka `Sidebar → Daily Entry`, klik **Import Excel**.
2. Pilih **Tipe Import:**
   - DPR (Daily Production Report)
   - Fuel Report
   - Daily Info Site
3. Drag & drop atau pilih file `.xlsx` / `.xls`.
4. Klik **Upload & Preview**.
5. Review data di halaman preview.
6. Klik **Confirm** untuk mengimpor ke database.

> Import berjalan di background queue untuk file besar. Pantau status di halaman preview.

---

### 3.3 Fuel Dashboard

**Path:** `Sidebar → Fuel`

Dashboard khusus monitoring konsumsi bahan bakar.

**Komponen:**

| Komponen | Deskripsi |
|----------|-----------|
| **KPI Fuel Hari Ini** | Total liter hari ini + MTD |
| **FCR Trend Chart** | Grafik Fuel Consumption Rate per alat |
| **Tabel per Alat** | Unit, Liter, Jam Kerja, FCR |

**Kolom tabel per alat:**

| Kolom | Keterangan |
|-------|------------|
| Unit | Kode alat (mis. E 071) |
| Liter | Konsumsi BBM hari ini |
| Jam Kerja | Total jam operasional |
| FCR | Fuel Consumption Rate (liter/jam) |

**Filter:** Pilih site dan tanggal, lalu klik **Terapkan**.

> Sistem otomatis mendeteksi anomali FCR (outlier 2σ) dan mengirim notifikasi ke pengguna terkait.

---

### 3.4 Equipment

**Path:** `Sidebar → Equipment` *(Admin)*

Halaman **Equipment Assignment** untuk menugaskan alat berat dari arkfleet-next ke PIT di site aktif.

#### Melihat Assignment

Tabel menampilkan alat yang sudah di-assign:

| Kolom | Keterangan |
|-------|------------|
| Kode Unit | Kode alat (mis. DZ 040) |
| Deskripsi | Nama/deskripsi alat |
| Tipe Plant | Jenis alat (Excavator, Dump Truck, dll.) |
| Project | Kode project/site |
| PIT | PIT penugasan |
| Tracking | Status aktif untuk tracking fuel/deployment |
| Material / Role / Order | Klasifikasi CCR *(site CCR)* — lihat di bawah |

#### Menambah Assignment

1. Klik **Assign Equipment**.
2. Drawer pencarian terbuka — ketik kode unit atau deskripsi.
3. Pilih alat dari hasil pencarian (data dari arkfleet-next API).
4. Pilih **PIT** tujuan.
5. Konfirmasi — alat muncul di tabel assignment.

#### Klasifikasi CCR *(site CCR-enabled)*

Untuk site yang memakai CCR Hourly, setiap unit yang di-assign juga harus diklasifikasi untuk grid hourly:

1. Di tabel assignment, klik **Klasifikasi CCR** pada baris alat.
2. Di modal, atur:
   - **Material Type** — Limestone, Shalestone, Coal, Overburden (OB), atau kosongkan untuk umum
   - **Equipment Role** — mis. loader, hauler, grader; untuk 022C trip: `excavator` (digger) atau `hauler` (truk)
   - **Display Order** — urutan kolom di heatmap/grid hourly (angka kecil = kiri)
   - **Tracking Aktif** — sertakan di ringkasan fleet hourly
3. Klik **Simpan**.

> Assign (pencarian ArkFleet) dan klasifikasi CCR adalah langkah terpisah. Unit bisa di-assign ke PIT sebelum peran material CCR-nya diketahui.

#### Menghapus Assignment

Klik ikon **Hapus** (🗑) di baris alat, konfirmasi penghapusan.

> Hanya alat yang ter-assign yang muncul di form Fuel dan Deployment daily entry.

---

### 3.5 Plan vs Actual

#### Monthly Plan

**Path:** `Sidebar → Monthly Plan`

Input target produksi bulanan per PIT.

**Membuat plan baru:**
1. Klik **Plan Baru**.
2. Pilih **Site**, **Tahun**, dan **Bulan**.
3. Simpan — Anda diarahkan ke halaman edit target.

**Mengisi target per PIT:**

| Metrik | Unit | Keterangan |
|--------|------|------------|
| OB | Bcm | Target overburden removal |
| Coal | Ton | Target coal getting |
| Stripping Ratio | — | Target SR (opsional) |

Grid menampilkan semua PIT di site dengan kolom target per metrik dan owner (Internal/Kontraktor).

1. Isi nilai target di grid.
2. Klik **Simpan Target**.

#### Achievement Calculation

Achievement % dihitung otomatis oleh **Calculation Engine** di backend:

```
Achievement % = (Actual MTD / Target MTD) × 100
```

- **Actual** — agregasi data produksi yang sudah *approved*
- **Target** — dari monthly plan per PIT
- Tidak disimpan mentah di database; selalu dihitung ulang untuk konsistensi

#### Variance Analysis

**Path:** `Sidebar → Variance`

Analisis selisih plan vs actual per PIT per bulan.

**Komponen:**

| Komponen | Deskripsi |
|----------|-----------|
| **Variance Table** | Tabel per PIT: target, actual, variance, achievement % |
| **Plan vs Actual Chart** | Grafik perbandingan visual |
| **Loss Contribution Chart** | Kontribusi kehilangan produksi dari jam hujan & licin |

**Filter:** Site, Tahun, Bulan → klik **Refresh**.

Data jam hujan/licin diambil dari tab **Info Site** daily entry.

---

### 3.6 CCR Hourly Production (021C/025C)

Modul monitoring produksi **per jam per alat** untuk site semen **021C** dan **025C**. Material utama: **Limestone (LS)** dan **Shalestone (SH)**. Menggantikan Google Sheets CCR hourly dengan grid terintegrasi, heatmap real-time, dan workflow persetujuan yang sama dengan Daily Entry.

**Submenu CCR Hourly** (`Sidebar → CCR Hourly`):

| Submenu | Path | Fungsi |
|---------|------|--------|
| **Hourly Entry** | `Sidebar → CCR Hourly → Hourly Entry` | Grid input tonase per jam per alat |
| **CCR Dashboard** | `Sidebar → CCR Hourly → CCR Dashboard` | KPI, heatmap, trend, fleet status |
| **Import CCR 022C** | `Sidebar → CCR Hourly → Import CCR 022C` | Import Excel trip-level *(site 022C — lihat §3.7)* |
| **Trip Entry 022C** | `Sidebar → CCR Hourly → Trip Entry 022C` | Input trip manual *(site 022C — lihat §3.7)* |

**Site CCR Hourly (LS/SH):** 021C (Limestone + Shalestone), 025C (Limestone).

#### CCR Dashboard

**Path:** `Sidebar → CCR Hourly → CCR Dashboard`

Dashboard monitoring produksi hourly dengan filter **Site**, **Tanggal**, dan **Material**.

| Komponen | Deskripsi |
|----------|-----------|
| **KPI Card** | Per material terpilih: **DTD** (actual vs plan), **MTD** (actual vs plan), **Jam Ini** (tonase jam berjalan vs target jam) |
| **Hourly Heatmap** | Tabel warna **jam × alat** — setiap sel menampilkan tonase (Mton) pada interval jam tersebut |
| **Trend Chart** | Grafik total tonase per jam sepanjang hari |
| **Fleet Status** | Ringkasan jumlah unit aktif per role (loader, hauler, grader) dari equipment assignment |

**Skema warna heatmap** (berdasarkan achievement vs target per jam):

| Warna | Kondisi |
|-------|---------|
| Hijau | Achievement ≥ 95% |
| Kuning | Achievement 70%–94% |
| Merah | Achievement < 70% |
| Transparan | Belum ada target harian dikonfigurasi |

**Filter:**
- **Site** — 021C, 025C (dan site CCR lain jika diaktifkan)
- **Tanggal**
- **Material** — Limestone (LS), Shalestone (SH)

**Export:** Tombol **Export Excel** dan **Export PDF** di toolbar.

> KPI dashboard memakai data hourly yang sudah **approved** (site-wide). Total draft di Daily Entry memakai agregasi per-entry live (lihat §3.2 Tab CCR Hourly).

#### Hourly Entry

**Path:** `Sidebar → CCR Hourly → Hourly Entry`

Grid input tonase per jam per alat — setara satu sel di Google Sheet CCR.

**Membuat CCR daily entry (hourly):**

1. Buka `Sidebar → CCR Hourly → Hourly Entry`.
2. Klik **Entry Baru**.
3. Isi form pembuatan:
   - **Tanggal** — tanggal produksi
   - **Site** — 021C atau 025C
   - **Material** — Limestone (LS) atau Shalestone (SH)
   - **Shift** — Siang / Malam
4. Klik **Buat & Lanjut Input** — Anda diarahkan ke halaman grid.

> Satu entry hourly unik per kombinasi **tanggal + site + material + shift**. Entry menempel pada `daily_entry` header yang sama (tanggal + site).

**Menggunakan grid hourly:**

| Dimensi | Isi |
|---------|-----|
| **Baris** | Interval jam (00:00–01:00 s/d 23:00–24:00) |
| **Kolom** | Alat ter-assign dari Equipment Assignment (mis. E 084, E 096) — hanya unit yang sudah diklasifikasi CCR |
| **Sel** | Tonase (Mton) pada jam tersebut — ketik angka langsung di sel |
| **Kolom D/Shift** | Total per baris jam (otomatis) |
| **Baris Total** | Total per kolom alat (otomatis) |

**Langkah input:**
1. Isi tonase per sel sesuai output alat per jam.
2. Klik **Simpan** — data tersimpan meski status masih Draft.
3. Setelah lengkap, ikuti workflow **Draft → Submit → Approve** (sama dengan Daily Entry).

**Fleet status:** Header grid menampilkan ringkasan armada yang ter-assign untuk material tersebut. Pastikan alat sudah diklasifikasi via `Sidebar → Equipment → Klasifikasi CCR`.

**Hubungan ke Daily Entry:** Untuk tanggal dan site yang sama, record hourly menempel pada daily entry yang sama. Buka `Daily Entry → [Entry] → Tab CCR Hourly` untuk melihat total harian tanpa input ulang.

#### Material Types (021C/025C)

| Material | Label | Site |
|----------|-------|------|
| `limestone` | Limestone (LS) | 021C, 025C |
| `shalestone` | Shalestone (SH) | 021C |

#### Per-hour Per-equipment Tracking

Setiap sel di grid merepresentasikan satu record unik: `(tanggal, site, alat, material, jam)`. Warna heatmap di dashboard dihitung dari:

```
Target per jam = daily_plan_tonnage / operating_hours_per_day
Achievement sel = tonase_actual / target_per_jam
```

**Kolom alat** berasal dari assignment di `Sidebar → Equipment` dengan **Material Type** dan **Display Order** diatur via **Klasifikasi CCR**. Jika grid menampilkan "Belum ada alat ter-assign", klasifikasi alat untuk site aktif terlebih dahulu.

---

### 3.7 CCR 022C Trip Production

Modul monitoring produksi **trip-level** (satu baris = satu ritase truk) untuk site batubara **022C GPK**. Material: **Overburden (OB)**, **Coal**, dan **Top Soil**. Data trip di-agregasi otomatis ke heatmap hourly dan — tergantung mode — ke tab Produksi Daily Entry.

**Perbedaan dengan CCR Hourly (021C/025C):**

| Dimensi | CCR Hourly (021C/025C) | CCR 022C |
|---------|------------------------|----------|
| Granularitas | Agregat per jam per alat | Per trip (ritase truk) |
| Material | Limestone, Shalestone | OB, Coal, Top Soil |
| Pasangan alat | Satu alat per kolom | Excavator × Hauler per trip |
| Volume harian | ~24 baris/jam × alat | ~250+ trip/hari |

#### Import CCR 022C

**Path:** `Sidebar → CCR Hourly → Import CCR 022C`

Import massal dari file Excel CCR 022C legacy (sheet **DATA TRIP**).

**Langkah:**
1. Buka halaman Import CCR 022C.
2. Pilih **Site** (022C).
3. Drag & drop atau pilih file `.xlsx` / `.xls` (sheet DATA TRIP akan di-parse otomatis).
4. Klik **Upload & Preview**.
5. Halaman **Preview** menampilkan:
   - Tanggal produksi terdeteksi
   - Total trip, rollup OB (BCM), rollup Coal (Mton)
   - Kode unit yang belum match ke arkfleet (jika ada)
   - Error parsing per baris (jika ada)
6. Review mapping — pastikan tanggal dan jumlah trip sesuai.
7. Klik **Import & Rollup** untuk mengonfirmasi — trip records disimpan dan diagregasi ke hourly heatmap.

> Import berjalan di background untuk file besar. Refresh halaman preview jika status masih `parsing`.

#### Trip Entry 022C

**Path:** `Sidebar → CCR Hourly → Trip Entry 022C`

Form input trip manual — untuk menambah satu ritase tanpa import Excel.

| Field | Keterangan |
|-------|------------|
| **Tanggal** | Tanggal produksi |
| **Shift** | Siang / Malam |
| **Excavator** | Alat gali (digger) — dari equipment assignment role `excavator` |
| **Hauler** | Truk angkut — dari equipment assignment role `hauler` |
| **Material** | OB, Coal, atau Top Soil |
| **Jam (0–23)** | Jam mulai interval trip |
| **Volume (BCM)** | Volume per trip |
| **% Load** | Persentase muatan truk |
| **Ret/Trip** | Jumlah ritase (default 1.0) |
| **Kapasitas Truk (BCM)** | Kapasitas truk (opsional) |

Klik **Simpan Trip** — trip langsung masuk ke agregasi hourly untuk heatmap.

#### Dashboard 022C

**Path:** `Sidebar → CCR Hourly → CCR Dashboard` *(pilih site 022C)*

Dashboard 022C menampilkan komponen khusus trip-site di atas heatmap hourly:

| Komponen | Deskripsi |
|----------|-----------|
| **KPI Cards (3 material)** | Satu kartu per material (OB, Coal, Top Soil) — masing-masing menampilkan DTD, MTD, dan **Jam Ini** |
| **Hourly Heatmap** | Agregat trip → tonase per jam per excavator (warna sama: hijau ≥95%, kuning 70–95%, merah <70%) |
| **Pairing Excavator × Hauler** | Panel collapse per excavator — daftar hauler yang melayani, jumlah ritase, total volume BCM, rata-rata % load |
| **Fleet Status** | Jumlah unit per role |
| **Rekonsiliasi Trip vs Manual** | Perbandingan Σ trip vs input manual OB/Coal di Daily Entry |

**Filter material:** Pilih OB, Coal, atau Top Soil untuk heatmap dan trend pada material tersebut.

#### Rekonsiliasi

Panel **Rekonsiliasi Trip vs Manual** di CCR Dashboard (site 022C) membandingkan:

| Metrik | Sumber Trip | Sumber Manual |
|--------|-------------|---------------|
| OB | Σ volume trip OB (BCM) | Tab Produksi Daily Entry |
| Coal | Σ volume trip Coal (Mton) | Tab Produksi Daily Entry |

**Indikator selisih (Δ):**
- Hijau — selisih < 0,01
- Kuning — selisih < 5% dari nilai manual
- Merah — selisih signifikan

#### Mode Production Source (Feature Flag)

Site 022C mendukung dua mode sumber data produksi harian OB/Coal, dikonfigurasi oleh Admin:

| Mode | Perilaku | Kapan dipakai |
|------|----------|---------------|
| **`parallel`** *(default)* | Trip data untuk analisis granular; OB/Coal harian tetap diinput manual di Daily Entry. Panel rekonsiliasi menandai selisih. | Fase transisi / dual-run |
| **`trip_derived`** | Tab Produksi OB/Coal **auto-populate** dari rollup trip. Input manual menjadi read-only. | Setelah data trip dipercaya |

Mode aktif ditampilkan sebagai tag di panel Rekonsiliasi (`parallel` atau `trip_derived`).

---

### 3.8 Procurement KPI

**Path:** `Sidebar → Procurement`

Dashboard KPI pengadaan dan material dari **ARK-GS** (sync SAP B1, 2× sehari pukul 06:05 & 12:05 WITA). Data di-cache 6 jam di Redis.

**Filter:** Site, Tahun, Bulan → **Terapkan**

#### Budget Performance

| Metrik | Deskripsi |
|--------|-----------|
| Budget Amount | Total anggaran plant |
| Actual Amount | Realisasi pengeluaran |
| Utilization % | % anggaran terpakai |

Tersedia juga **CAPEX Budget** terpisah.

#### GRPO Completion

| Metrik | Deskripsi |
|--------|-----------|
| PO Amount | Nilai Purchase Order terkirim |
| GRPO Amount | Nilai barang diterima (Goods Receipt) |
| Completion % | % PO yang sudah diterima |
| Status | Hijau (≥80%) / Merah (<80%) |

#### NPI Efficiency

| Metrik | Deskripsi |
|--------|-----------|
| Incoming Qty | Barang masuk |
| Outgoing Qty | Material issue |
| NPI Index | Incoming / Outgoing — semakin rendah semakin efisien |

**Chart tambahan:**
- PO vs GRPO comparison chart
- NPI In/Out chart

Badge **Last Synced** menampilkan waktu sync terakhir dari ARK-GS.

---

### 3.9 Reports

**Path:** `Sidebar → Reports`

#### Laporan Harian (DPR)

Generate laporan produksi harian per site.

1. Pilih **Site** dan **Tanggal**.
2. Klik **Download PDF** atau **Download Excel**.
3. File otomatis terunduh.

#### Laporan Custom

**Path:** `Sidebar → Reports → Buat Laporan Custom`

1. Klik **Buat Laporan Custom**.
2. Atur rentang tanggal (dari–sampai).
3. Filter opsional: site, PIT.
4. Pilih format PDF atau Excel.
5. Generate dan download.

#### Laporan Konsolidasi

**Path:** `Sidebar → Reports → Buka Laporan Konsolidasi`

Ringkasan multi-site dan multi-periode mencakup:
- Produksi (OB, Coal)
- Fuel consumption
- Equipment deployment
- Site info (cuaca, manpower)

1. Pilih site (bisa multiple).
2. Atur periode.
3. Generate laporan konsolidasi.

---

### 3.10 Notifications

#### Notification Bell

**Lokasi:** Ikon 🔔 di header kanan atas.

- Badge merah menampilkan jumlah notifikasi belum dibaca.
- Klik ikon untuk membuka halaman notifikasi.
- Auto-refresh setiap 60 detik.

#### Halaman Notifikasi

**Path:** `Sidebar → Notifikasi`

| Aksi | Fungsi |
|------|--------|
| **Tandai dibaca** | Tandai satu notifikasi sebagai dibaca |
| **Tandai Semua Dibaca** | Tandai semua sekaligus |

Notifikasi belum dibaca ditampilkan dengan background hijau muda.

#### Jenis Alert

| Jenis | Trigger | Jadwal |
|-------|---------|--------|
| **Achievement Di Bawah Target** | OB/Coal achievement < 90% | Setiap jam :30 (07:00–18:00 WITA) |
| **Anomali Fuel Consumption** | FCR outlier (z-score > 2σ) | Setiap jam :30 (07:00–18:00 WITA) |
| **Reminder Input Data** | Belum ada entry untuk hari ini | 20:00 WITA |
| **Ringkasan Harian** | Summary KPI produksi | 07:00 WITA |

> Jika Telegram bot dikonfigurasi, notifikasi juga dikirim ke channel Telegram.

---

## 4. Master Data (Admin)

Semua menu master data memerlukan peran **Admin**.

### 4.1 Sites & PITs

**Sites** — `Sidebar → Sites`

| Field | Keterangan |
|-------|------------|
| Kode | Kode site (mis. 022C) |
| Nama | Nama project |
| Aktif | Status aktif/nonaktif |

**PITs** — `Sidebar → PITs`

| Field | Keterangan |
|-------|------------|
| Kode | Kode PIT (mis. KF1, KF2) |
| Site | Site induk |
| Owner | Internal / Kontraktor |

### 4.2 Shifts

**Path:** `Sidebar → Shifts`

Kelola shift operasional:

| Shift | Label |
|-------|-------|
| `day` | Siang |
| `night` | Malam |

### 4.3 Fuel Types & Prices

**Jenis BBM** — `Sidebar → Jenis BBM`

| Field | Keterangan |
|-------|------------|
| Nama | Nama jenis (mis. Solar Industri) |
| Kode | Kode singkat |
| Aktif | Status |

**Harga BBM** — `Sidebar → Harga BBM`

| Field | Keterangan |
|-------|------------|
| Jenis BBM | Tipe bahan bakar |
| Harga | Harga per liter (IDR) |
| Berlaku dari | Tanggal efektif |

### 4.4 Users & Roles

**Path:** `Sidebar → Pengguna`

| Field | Keterangan |
|-------|------------|
| Nama | Nama lengkap |
| Email | Alamat email (login) |
| Username | Username alternatif (opsional) |
| Role | Admin / Supervisor / Management / Fuel Officer |
| Site | Site default pengguna |

**Peran dan permission:**

| Permission | Admin | Supervisor | Management | Fuel Officer |
|------------|:-----:|:----------:|:----------:|:------------:|
| Master data | ✅ | — | — | — |
| Equipment assign | ✅ | — | — | — |
| Entry create | ✅ | ✅ | — | ✅ |
| Entry approve | ✅ | ✅ | — | — |
| Dashboard view | ✅ | ✅ | ✅ | ✅ |
| Plan manage | ✅ | — | ✅ | — |
| Report generate | ✅ | ✅ | ✅ | — |

### 4.5 Equipment Assignment

Lihat [§3.4 Equipment](#34-equipment) — hanya Admin yang dapat mengelola assignment alat ke PIT.

Untuk CCR hourly, assignment juga mendukung field tambahan (diatur via modal **Klasifikasi CCR** pada setiap baris):
- **Material Type** — limestone, shalestone, coal, overburden (ob), top_soil
- **Equipment Role** — loader, hauler, grader; untuk trip 022C: excavator (digger), hauler (truk)
- **Display Order** — urutan kolom di grid hourly

---

## 5. Penggunaan Mobile

### 5.1 Instalasi PWA

Lihat [§2.5 Instalasi PWA](#25-instalasi-pwa-mobile).

Setelah terinstal, aplikasi berjalan fullscreen tanpa address bar browser — ideal untuk supervisor di lapangan.

### 5.2 Mode Offline

ARKA MineOps mendukung input data saat tidak ada koneksi internet.

**Cara kerja:**
1. Saat offline, banner merah muncul di atas halaman: *"Mode offline — X entri dalam antrian"*.
2. Buat atau edit daily entry seperti biasa — data disimpan di **IndexedDB** browser.
3. Saat koneksi kembali, banner berubah kuning: *"Offline sync: X entri menunggu sinkronisasi"*.
4. Klik tombol **Sync** di header (desktop) untuk mengirim antrian ke server.
5. Sinkronisasi bersifat **idempotent** (berdasarkan UUID) — aman untuk retry.

> Pastikan entry di-sync sebelum logout atau clear browser data.

### 5.3 Site Selector di Mobile

Di layar kecil, Site Selector berada di menu **⋯** (titik tiga) di header kanan:
1. Ketuk **⋯**.
2. Pilih site dari dropdown di dalam menu.
3. Site aktif langsung berubah.

### 5.4 Navigation Drawer

Di mobile (< 1024px), sidebar disembunyikan dan diganti drawer:

1. Ketuk ikon **☰** (hamburger) di kiri atas header.
2. Drawer menu terbuka dari kiri.
3. Ketuk menu yang diinginkan — drawer otomatis tertutup.
4. Ketuk **✕** atau area luar drawer untuk menutup.

**Tips mobile:**
- Form input menggunakan keyboard numerik untuk field angka.
- Tombol aksi cukup besar untuk sentuhan jari.
- Gunakan mode landscape untuk grid hourly yang lebar.
- Di site CCR-enabled, wizard Daily Entry mobile menyertakan langkah **CCR Hourly** (read-only) yang menampilkan total harian dari input hourly.

---

## 6. Troubleshooting

### 6.1 Masalah Umum

| Masalah | Penyebab | Solusi |
|---------|----------|--------|
| Tidak bisa login | Email/password salah | Pastikan email/username benar; hubungi Admin untuk reset |
| Halaman kosong setelah login | Cache browser usang | Hard refresh (Ctrl+Shift+R) atau clear cache |
| Data dashboard tidak update | Cache Redis / belum approve | Pastikan entry sudah di-approve; tunggu 60 detik auto-refresh |
| Equipment tidak muncul di form fuel | Belum di-assign | Admin: assign alat di `Sidebar → Equipment` |
| Duplikasi entry ditolak | Entry sudah ada untuk tanggal+site | Edit entry existing, jangan buat baru |
| Import Excel gagal | Format file tidak sesuai | Pastikan tipe import benar (DPR/Fuel/Site Info) dan format .xlsx |
| Procurement data kosong | ARK-GS belum sync | Cek badge Last Synced; data sync 06:05 & 12:05 WITA |
| Offline sync gagal | Koneksi terputus saat sync | Pastikan online, klik Sync lagi |
| Menu Master Data tidak muncul | Bukan role Admin | Hubungi Admin untuk perubahan role |
| Heatmap CCR kosong | Belum ada data hourly / plan / alat belum diklasifikasi | Input data hourly; set material daily plan; klasifikasi alat via Klasifikasi CCR |
| Tab CCR Hourly kosong di Daily Entry | Belum ada record hourly untuk tanggal+site itu | Isi grid via `Sidebar → CCR Hourly → Hourly Entry` |
| Grid hourly tanpa kolom alat | Unit belum diklasifikasi CCR | Admin: `Sidebar → Equipment` → **Klasifikasi CCR** per unit |
| Import CCR 022C gagal | Format file / sheet salah | Pastikan file Excel berisi sheet **DATA TRIP** dan site 022C dipilih |
| Unit tidak match saat import 022C | Kode alat belum di arkfleet/assignment | Assign & klasifikasi alat di Equipment; import ulang atau input manual via Trip Entry |
| Rekonsiliasi trip vs manual merah | Selisih signifikan antara trip rollup dan input manual | Cek data trip & tab Produksi; pastikan mode `parallel`/`trip_derived` sesuai kebijakan site |
| Panel pairing kosong | Belum ada trip records untuk tanggal itu | Import Excel atau tambah trip via Trip Entry 022C |

### 6.2 Error Umum

| Error | Arti | Tindakan |
|-------|------|----------|
| 403 Forbidden | Tidak punya akses | Minta Admin menyesuaikan role/permission |
| 419 Page Expired | Sesi habis | Login ulang |
| 422 Validation Error | Data tidak valid | Periksa field yang ditandai merah |
| 500 Server Error | Error server | Coba lagi; hubungi support jika berlanjut |

### 6.3 Kontak Support

| Kanal | Detail |
|-------|--------|
| **IT Support PT. Arkananta** | Hubungi tim IT internal |
| **URL Aplikasi** | https://vps-iwan1.tail8334ce.ts.net |
| **Login demo** | admin@mineops.test / password |

Saat melapor masalah, sertakan:
1. URL halaman yang bermasalah
2. Site dan tanggal yang dipilih
3. Peran (role) akun Anda
4. Screenshot error (jika ada)
5. Browser dan perangkat yang digunakan

---

*Dokumen ini merupakan panduan resmi pengguna ARKA MineOps. Untuk detail teknis arsitektur, lihat `docs/architecture.md` dan `docs/concept.md`.*
