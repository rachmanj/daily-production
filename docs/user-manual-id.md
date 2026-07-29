# Panduan Pengguna ARKA MineOps

> **Versi:** 1.0 · **Terakhir diperbarui:** 29 Juli 2026  
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

Selain produksi harian, platform ini juga mencakup monitoring fuel, plan vs actual, procurement KPI (dari SAP B1 via ARK-GS), CCR hourly production, dan laporan otomatis PDF/Excel.

### 1.2 Ringkasan Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| **Executive Dashboard** | KPI produksi (OB, Coal, SR, Fuel), trend chart, status alat, produksi per-PIT |
| **Daily Entry** | Input harian terpadu: produksi, fuel, deployment, info site |
| **Fuel Dashboard** | Monitoring konsumsi BBM per alat, FCR, trend |
| **CCR Hourly** | Produksi per jam per alat (Limestone, Shalestone) dengan heatmap |
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
| **CCR Hourly** | `Sidebar → CCR Hourly` | Submenu Hourly Entry & CCR Dashboard |
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
5. Anda diarahkan ke halaman edit dengan empat tab.

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

#### Menambah Assignment

1. Klik **Assign Equipment**.
2. Drawer pencarian terbuka — ketik kode unit atau deskripsi.
3. Pilih alat dari hasil pencarian (data dari arkfleet-next API).
4. Pilih **PIT** tujuan.
5. Konfirmasi — alat muncul di tabel assignment.

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

### 3.6 CCR Hourly Production

Modul monitoring produksi **per jam per alat** untuk material non-coal (Limestone, Shalestone). Menggantikan Google Sheets CCR di site 021C dan 025C.

#### CCR Dashboard

**Path:** `Sidebar → CCR Hourly → CCR Dashboard`

| Komponen | Deskripsi |
|----------|-----------|
| **KPI Card** | DTD & MTD actual vs plan, achievement %, target per jam |
| **Heatmap** | Grid jam × alat, warna merah→hijau berdasarkan achievement |
| **Trend Chart** | Total tonase per jam |
| **Fleet Status** | Jumlah unit per role (loader, hauler, grader) |

**Filter:**
- **Site** — 021C, 025C (site CCR-enabled)
- **Tanggal**
- **Material** — Limestone (LS), Shalestone (SH)

**Export:** Tombol **Export Excel** dan **Export PDF** di toolbar.

#### Hourly Entry

**Path:** `Sidebar → CCR Hourly → Hourly Entry`

Grid input tonase per jam per alat — setara satu sel di Google Sheet CCR.

**Membuat entry hourly:**
1. Klik **Entry Baru**.
2. Pilih tanggal dan site.
3. Buka entry → grid menampilkan:
   - **Baris** = interval jam (00:00–01:00 s/d 23:00–24:00)
   - **Kolom** = alat ter-assign (E 084, E 096, dll.)
   - **Sel** = tonase (Mton) pada jam tersebut

4. Isi tonase per sel.
5. Pilih **Material** (Limestone / Shalestone) dan **Shift**.
6. Simpan — mengikuti workflow draft → submit → approve yang sama.

#### Material Types

| Material | Label | Site |
|----------|-------|------|
| `limestone` | Limestone (LS) | 021C, 025C |
| `shalestone` | Shalestone (SH) | 021C |
| `coal` | Coal | *(reserved)* |
| `other` | Lainnya | — |

#### Per-hour Per-equipment Tracking

Setiap sel di grid merepresentasikan satu record unik: `(tanggal, site, alat, material, jam)`. Warna heatmap di dashboard dihitung dari:

```
Target per jam = daily_plan_tonnage / operating_hours_per_day
Achievement sel = tonase_actual / target_per_jam
```

---

### 3.7 Procurement KPI

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

### 3.8 Reports

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

### 3.9 Notifications

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

Untuk CCR hourly, assignment juga mendukung field tambahan:
- **Material Type** — limestone, shalestone
- **Equipment Role** — loader, hauler, grader
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
| Heatmap CCR kosong | Belum ada data hourly / plan | Input data hourly dan set material daily plan |

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
