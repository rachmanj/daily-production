# Brainstorming Konsep: Aplikasi Integrasi Laporan Operasional Tambang

## Latar Belakang

PT. Arkananta adalah kontraktor pertambangan batubara di Site #022C Graha Panca Karsa Coal Project, Melak, Kalimantan Timur. Saat ini, laporan operasional harian dikirim via email dalam bentuk file Excel setiap hari. Ini tidak efisien — manajemen harus buka email, download attachment, dan gabungkan data secara manual.

**Tujuan:** Buat konsep aplikasi web yang mengintegrasikan semua laporan ini dalam satu dashboard, sehingga data cukup diinput/upload sekali dan semua stakeholder bisa langsung melihat data real-time tanpa perlu kirim-kirim email.

**Stakeholder:** Manajemen proyek, supervisor site, admin fuel, operator alat berat.

## 3 Jenis Laporan yang Perlu Diintegrasikan

### 1. DPR (Daily Production Report)
File: `01. DPR 31.05.26 #022C.xlsx`
- Sheet "Production", 778 baris × 156 kolom
- **Data harian (per tanggal):**
  - Over Burden Removal (Bcm): Plan GPK/ARKA, Actual Day/Night Shift, MTD, YTD, PTD, Achievement %
  - Coal Getting (ton): Plan GPK/ARKA, Actual Day/Night Shift, MTD, Achievement %
  - Stripping Ratio: MTD, YTD, PTD
  - Rain & Slippery (hours)
  - Fuel Consumption: Daily Used Fuel (liter), Fuel Factor
- **Detail Truck Count per hari:**
  - Overburden, Top Soil, Top Soil Outpit, OB Outpit, MUD, High Ash Coal, In pit
  - Coal Hauling
- **Fuel & Coal Plan vs Actual**
- **Equipment hours**

### 2. Daily Info Site (Laporan Pagi)
File: `DAILY INFO 01 JUNE SITE 022C.xlsx`
- Sheet "INFO SITE", 412 baris × 63 kolom
- Dokumen resmi: ARKA/ENG/IV/12.01
- **Production Summary (Day/Night/Daily):**
  - PIT1 GPK, PIT2 GPK — OB Removal
  - Coal Hauling by ARKA, Coal Hauling by GPK (IWJ SLS)
  - Coal Getting Total
- **Equipment Assignment per Shift:**
  - Excavator (E 071, E 076, etc.) — Shift, Prod. OB, Prod. Coal
  - Overburden Hauler (A 40 G, HM 400, A 60 H, 740 GC, 773 E)
  - Coal Hauler (HINO 500, HINO 700, IWJ, SLS)
- **Tambahan:** Weather, Manpower, Safety, Equipment status, fuel stock

### 3. Fuel Report (Laporan Pemakaian Solar)
File: `1.FUEL REPORT MEI 2026.xlsx`
- 39 sheet: Summary, Daily Fuel Usage, FCR, REPORT, MONTHLY ALL, MONTHLY ARKA, PENERIMAAN, 31 sheet harian, Sheet2
- **Summary Daily Used:** Per equipment per hari
  - Unit, Model/Type, No. Asset, HM/KM (Awal/Akhir), Jam kerja
  - Pemakaian Fuel: Siang & Malam (per tanggal)
- **Daily Fuel Usage for Production:**
  - ARKA Fuel Usage: Waste Loading, Waste Hauling, Dewatering Pump, General & Support
  - Breakdown support: Sloping/breaking (Exca Support), Spreading/clearing (DZ Support), dll
- **FCR (Fuel Consumption Ratio)**
- **Fuel Disbursement (Sheet2):** No. Asset, HM/KM Awal, Jumlah, No GI
- **Equipment list (40+ unit):**
  - Excavator: E 062 (Doosan S500LC), E 071 (Hitachi EX1200-6), E 077 (Hitachi ZX870LCH-5G), E 081 (Hyundai R850LC-9), E 088 (Komatsu PC210-10M0), E 090-E 094 (Komatsu PC1250SP-11R / Hitachi ZX890LCH-7G), E 085
  - Dozer: DZ 040, DZ 042, DZ 043, DZ 044
  - ADT: 009, 011, 013, 015, 016, 020, 021, 022, 023, 024, 025, 027, 028
  - Dumptruck: T 112, T 115, T 116, T 117, T 118, T 119, T 121, T 122, T 132, T 133
  - Lainnya: ST 002, V 072, V 077, VA 069, VA 075, WT 003

## Kebutuhan Aplikasi

### Fitur Inti
1. **Master Data Management:**
   - Equipment/asset registry dengan tipe, model, HM/KM tracking
   - Site/PIT configuration
   - Shift definition (Day/Night)
   - Fuel type dan price tracking

2. **Daily Data Entry:**
   - Form input produksi harian (OB Removal, Coal Getting per PIT)
   - Form input fuel usage per equipment per shift
   - Form input daily info site (cuaca, manpower, safety)
   - Bisa juga: upload/parse Excel langsung (migrasi dari workflow lama)

3. **Dashboard & Reporting:**
   - Dashboard real-time: Produksi hari ini, MTD, achievement vs plan
   - Grafik tren: OB, Coal, Stripping Ratio, Fuel Consumption
   - Fuel dashboard: konsumsi per equipment, FCR trend
   - Equipment utilization & availability
   - Daily report auto-generate (PDF/Excel export)

4. **Plan vs Actual Tracking:**
   - Input monthly plan (OB target, Coal target)
   - Auto-calculate achievement %
   - Variance analysis

5. **Multi-user & Role-based Access:**
   - Admin (input plan, manage master data)
   - Operator/Supervisor (input data harian)
   - Management (view dashboard & reports)
   - Fuel officer (input fuel data)

6. **Notification & Alert:**
   - Notifikasi ketika achievement di bawah target
   - Alert fuel consumption anomaly
   - Daily summary auto-kirim ke Telegram/WhatsApp (opsional)

## Tech Stack Preference

**Backend:** Laravel 11+ (PHP)
**Frontend:** Inertia.js + React + Ant Design (ProTable, Charts)
**Database:** MySQL 8
**Deployment:** Ubuntu VPS, Tailscale networking
**Integrasi:** OpenRouter API key tersedia jika perlu fitur AI

## Yang Perlu Dihasilkan

Buat **konsep aplikasi komprehensif** dalam format markdown plan document, mencakup:

1. **Nama aplikasi** yang catchy dan profesional
2. **Arsitektur overview** — bagaimana ketiga laporan terintegrasi
3. **Entity Relationship Diagram** (Mermaid) — tabel-tabel database utama
4. **Daftar modul/fitur** dengan deskripsi
5. **UX flow** — bagaimana user journey dari login sampai generate report
6. **Wireframe konsep** — layout dashboard utama dan form input
7. **Tech stack detail** — library, package, approach
8. **Fase implementasi** — breakdown per fase dengan estimasi
9. **Pertimbangan khusus:**
   - Bagaimana handling data historis (ratusan file Excel existing)
   - Strategi migrasi dari workflow email ke aplikasi
   - Mobile-friendly (supervisor di site pakai HP)
   - Offline capability (site mungkin sinyal jelek)

Ini adalah GREENFIELD project — tidak ada codebase existing. Brainstorming konsep dulu, tidak perlu mulai coding.

Gunakan model Opus untuk deep thinking dan konsep yang matang. Output sebagai file markdown di `docs/concept.md`.
