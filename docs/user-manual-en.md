# ARKA MineOps User Manual

> **Version:** 1.0 · **Last Updated:** July 29, 2026  
> PT. Arkananta · Integrated Mining Operations Dashboard

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Getting Started](#2-getting-started)
3. [Application Modules](#3-application-modules)
4. [Master Data (Admin)](#4-master-data-admin)
5. [Mobile Usage](#5-mobile-usage)
6. [Troubleshooting](#6-troubleshooting)

---

## 1. Introduction

### 1.1 What is ARKA MineOps?

**ARKA MineOps** (*Integrated Mining Operations Dashboard*) is an integrated web platform for managing mining operations at PT. Arkananta. It replaces the manual **Excel → Email → Download → Merge** reporting cycle with a single source of truth that updates in real time.

Product philosophy: **"Enter once, view everywhere, in real time."**

ARKA MineOps unifies three legacy daily reports:

| Legacy Report | MineOps Module |
|---------------|----------------|
| DPR (Daily Production Report) | **Produksi** tab |
| Fuel Report | **Fuel** tab |
| Daily Info Site | **Info Site** + **Deployment** tabs |

Beyond daily production, the platform includes fuel monitoring, plan vs actual analysis, procurement KPIs (from SAP B1 via ARK-GS), CCR hourly production, and automated PDF/Excel reports.

### 1.2 Key Features Overview

| Feature | Description |
|---------|-------------|
| **Executive Dashboard** | Production KPIs (OB, Coal, SR, Fuel), trend charts, equipment status, per-PIT production |
| **Daily Entry** | Unified daily input: production, fuel, deployment, site info |
| **Fuel Dashboard** | Per-equipment fuel consumption, FCR, trends |
| **CCR Hourly** | Hourly per-equipment production (Limestone, Shalestone) with heatmap |
| **Plan vs Actual** | Monthly PIT targets, achievement %, variance analysis |
| **Procurement KPI** | Budget, GRPO, NPI from ARK-GS (SAP B1) |
| **Reports** | Daily, custom period, multi-site consolidated reports |
| **Notifications** | Achievement alerts, fuel anomalies, input reminders |
| **Multi-site** | Enterprise-wide: 022C, 021C, 017C, 011C, 025C, 026C, 023C, APS |
| **PWA & Offline** | Mobile install, offline draft entry, automatic sync |

### 1.3 Access & Credentials

| Item | Value |
|------|-------|
| **Application URL** | https://vps-iwan1.tail8334ce.ts.net |
| **Demo email** | `admin@mineops.test` |
| **Demo password** | `password` |
| **Alternative login** | Username (if set by admin) |
| **Timezone** | WITA (Asia/Makassar) |

> **Security note:** Change the default password immediately after first login. Production accounts are managed by Admin via **Sidebar → Pengguna**.

### 1.4 User Roles

| Role | Primary Access |
|------|----------------|
| **Admin** | Full access: master data, users, equipment assignment, all modules |
| **Supervisor** | Create & approve daily entries, dashboard, reports |
| **Management** | Dashboard, monthly plans, reports, variance |
| **Fuel Officer** | Create daily entries (fuel focus), fuel dashboard |

---

## 2. Getting Started

### 2.1 Login Process

1. Open a browser (Chrome, Edge, or Safari recommended).
2. Navigate to: **https://vps-iwan1.tail8334ce.ts.net**
3. You will be redirected to the login page.

**Login page layout:**
- Split-screen design: gradient brand panel on the left, login form card on the right.
- Title: **ARKA MineOps** — *Sistem Manajemen Operasional Tambang*
- Form fields:
  - **Email atau Username** — enter email address or username
  - **Password** — enter password
  - **Ingat saya** (Remember me) — check for persistent session
  - **Masuk** (Sign in) button

4. Click **Masuk**. On success, you are redirected to the **Executive Dashboard**.
5. On failure, an error message appears below the relevant field.

> **Screenshot description:** The login page shows a white form card over a dark blue gradient background with the ⛏️ ARKA MineOps branding.

### 2.2 Dashboard Overview

After login, the main page is the **Executive Dashboard** (`Sidebar → Dashboard`).

Main components:

| Area | Function |
|------|----------|
| **Top filters** | Site selector, date picker, **Terapkan** (Apply) button |
| **KPI Cards** | OB Removal, Coal Getting, Stripping Ratio (MTD), Fuel Today |
| **Trend Chart** | Daily production trend line chart |
| **Equipment Status** | Fleet summary: active, standby, breakdown |
| **Per-PIT Chart** | Production breakdown by PIT |
| **Drill-down** | Per-PIT detail in a side drawer |

Dashboard data auto-refreshes every 60 seconds.

### 2.3 Site Selector (Multi-site Support)

ARKA MineOps is designed **enterprise-wide** — users can switch between operational sites.

**Location:** Top-right header (desktop) or **⋯** menu (mobile).

**How to use:**
1. Click the **Site Selector** dropdown in the header.
2. Select a site, e.g. `022C — GPK Project`.
3. The active site is saved in your session and affects:
   - Dashboard data
   - Daily entry lists
   - Equipment assignments
   - Report filters

**Operational sites:**

| Code | Name |
|------|------|
| 022C | GPK Project |
| 021C | SBI Project |
| 017C | KPUC Project |
| 011C | Kitadin Project |
| 025C | SBI Project 2 |
| 026C | CEP Project |
| 023C | Bharinto Project |
| APS | Arka Project Support |

### 2.4 Sidebar Navigation

Main menu in the left sidebar (desktop) or drawer (mobile):

| Menu | Path | Description |
|------|------|-------------|
| **Dashboard** | `Sidebar → Dashboard` | Executive KPI dashboard |
| **Daily Entry** | `Sidebar → Daily Entry` | Create & manage daily entries |
| **CCR Hourly** | `Sidebar → CCR Hourly` | Hourly Entry & CCR Dashboard submenu |
| **Fuel** | `Sidebar → Fuel` | Fuel consumption dashboard |
| **Equipment** | `Sidebar → Equipment` | Equipment-to-PIT assignment *(Admin)* |
| **Sites** | `Sidebar → Sites` | Site master data *(Admin)* |
| **PITs** | `Sidebar → PITs` | PIT master data *(Admin)* |
| **Shifts** | `Sidebar → Shifts` | Shift master data *(Admin)* |
| **Jenis BBM** | `Sidebar → Jenis BBM` | Fuel type master data *(Admin)* |
| **Harga BBM** | `Sidebar → Harga BBM` | Fuel price master data *(Admin)* |
| **Pengguna** | `Sidebar → Pengguna` | User & role management *(Admin)* |
| **Monthly Plan** | `Sidebar → Monthly Plan` | Monthly production targets |
| **Variance** | `Sidebar → Variance` | Plan vs actual variance analysis |
| **Procurement** | `Sidebar → Procurement` | Procurement KPIs from SAP B1 |
| **Reports** | `Sidebar → Reports` | Generate & download reports |
| **Notifikasi** | `Sidebar → Notifikasi` | System notifications |

**Top-right header (desktop):**
- **Sync** button — sync offline data
- **Site Selector**
- **Bell icon** — unread notifications
- **Theme Toggle** — dark/light mode
- **Avatar** — profile & logout

### 2.5 PWA Installation (Mobile)

ARKA MineOps supports installation as a Progressive Web App (PWA) for native-like access on smartphones.

**Android (Chrome):**
1. Open the application URL in Chrome.
2. Tap menu **⋮** → **Install app** / **Add to Home screen**.
3. Confirm installation.
4. The ARKA MineOps icon appears on your home screen.

**iOS (Safari):**
1. Open the URL in Safari.
2. Tap the **Share** button.
3. Select **Add to Home Screen**.
4. Name the app and tap **Add**.

---

## 3. Application Modules

### 3.1 Dashboard

**Path:** `Sidebar → Dashboard`

#### Executive Dashboard

Displays operational KPI summary for the selected site and date.

**KPI Cards:**

| KPI | Unit | Description |
|-----|------|-------------|
| **OB Removal** | Bcm | Overburden removal today + MTD + achievement % |
| **Coal Getting** | Ton | Coal production today + MTD + achievement % |
| **Stripping Ratio (MTD)** | — | OB/Coal ratio for the current month |
| **Fuel Today** | Liter | Total fuel consumption today + MTD |

**Trend Charts:**
- Line chart showing daily production trends for the selected period.
- Hover over data points for detailed values.

**Per-PIT Production:**
- Bar chart of production by PIT (OB and Coal).
- Click **Drill-down** to open a per-PIT detail drawer.

**Equipment Status:**
- Fleet status summary: **Active**, **Standby**, **Breakdown**, and total units.
- Data sourced from daily equipment deployment records.

**Filters:**
1. Select **Site** from the dropdown.
2. Select **Date** via DatePicker.
3. Click **Terapkan** (Apply).

---

### 3.2 Daily Entry

**Path:** `Sidebar → Daily Entry`

Core module for daily operational data entry — replacing three separate Excel files.

#### Creating a Daily Entry

1. Open `Sidebar → Daily Entry`.
2. Click **Entry Baru** (+) button.
3. Fill in:
   - **Tanggal Produksi** (Production Date)
   - **Site**
4. Click **Buat Entry** (Create Entry).
5. You are redirected to the edit page with four tabs.

> One entry per **date + site** combination. Duplicates are rejected.

#### Production Tab

**Path:** `Daily Entry → [Entry] → Tab Produksi`

Enter production data per PIT and shift.

| Field | Description |
|-------|-------------|
| **PIT** | Operational PIT |
| **Shift** | Day (Siang) / Night (Malam) |
| **Activity** | OB, Coal, Top Soil, MUD, High Ash Coal |
| **Volume** | Production quantity (Bcm for OB, Ton for Coal) |

**Steps:**
1. Add a production row (+).
2. Fill in PIT, shift, activity, and volume.
3. Click **Simpan Produksi** (Save Production).

#### Fuel Tab

**Path:** `Daily Entry → [Entry] → Tab Fuel`

Enter fuel consumption per heavy equipment unit.

| Field | Description |
|-------|-------------|
| **Unit** | Equipment code (from equipment assignments) |
| **Shift** | Day / Night |
| **Category** | Waste Loading, Waste Hauling, Dewatering, General & Support |
| **Fuel Type** | Fuel type (from master data) |
| **Liters** | Consumption amount (liters) |
| **Working Hours** | Equipment operating hours |

**Steps:**
1. Add a fuel row.
2. Select unit from the assigned equipment list for the active site.
3. Fill in category, fuel type, liters, and working hours.
4. Click **Simpan Fuel** (Save Fuel).

#### Equipment Deployment Tab

**Path:** `Daily Entry → [Entry] → Tab Deployment`

Record equipment placement per PIT and shift (replaces the equipment section of Daily Info Site).

| Field | Description |
|-------|-------------|
| **Unit** | Equipment code |
| **PIT** | Assignment location |
| **Shift** | Day / Night |
| **Status** | Active / Standby / Breakdown |
| **RFU** | Ready For Use (yes/no) |

**Steps:**
1. Add a deployment row.
2. Fill in unit, PIT, shift, and status.
3. Click **Simpan Deployment** (Save Deployment).

#### Site Info Tab

**Path:** `Daily Entry → [Entry] → Tab Info Site`

Enter general daily site information (replaces the non-equipment section of Daily Info Site).

| Field | Description |
|-------|-------------|
| **Weather** | Clear / Rain / Cloudy |
| **Rain Hours** | Duration of rain (hours) |
| **Slippery Hours** | Slippery condition duration (hours) |
| **Manpower Plan** | Planned workforce |
| **Manpower Actual** | Actual workforce |
| **Fuel Stock (Liters)** | On-site fuel stock |
| **Safety Notes** | HSE notes |

**Steps:**
1. Fill in all relevant fields.
2. Click **Simpan Info Site** (Save Site Info).

#### Workflow: Draft → Submit → Approve

Every daily entry follows an approval workflow:

```
Draft → Submit → Approve
              ↘ Reject → back to Draft
```

| Status | Label | Description |
|--------|-------|-------------|
| `draft` | Draf | Editable |
| `submitted` | Disubmit | Awaiting approval |
| `approved` | Disetujui | Final; KPI cache recalculated |

**Workflow steps:**

1. **Draft** — complete all tabs, save each tab individually.
2. Open the entry detail page (`Daily Entry → [Entry] → View`).
3. Click **Submit** — status changes to *Disubmit*, entry becomes read-only.
4. **Approver** (Supervisor/Admin) clicks **Approve** or **Reject**.
5. After **Approve**, data is included in MTD/YTD/SR/FCR calculations.

> Each tab is saved separately via its own **Simpan** button. Ensure all tabs are saved before Submit.

#### Excel Import

**Path:** `Sidebar → Daily Entry → Import Excel` (Upload button)

Bulk input alternative from legacy Excel files.

**Steps:**
1. Open `Sidebar → Daily Entry`, click **Import Excel**.
2. Select **Import Type:**
   - DPR (Daily Production Report)
   - Fuel Report
   - Daily Info Site
3. Drag & drop or select a `.xlsx` / `.xls` file.
4. Click **Upload & Preview**.
5. Review data on the preview page.
6. Click **Confirm** to import into the database.

> Large files are processed in a background queue. Monitor status on the preview page.

---

### 3.3 Fuel Dashboard

**Path:** `Sidebar → Fuel`

Dedicated dashboard for fuel consumption monitoring.

**Components:**

| Component | Description |
|-----------|-------------|
| **Fuel Today KPI** | Total liters today + MTD |
| **FCR Trend Chart** | Fuel Consumption Rate chart per equipment |
| **Per-Equipment Table** | Unit, Liters, Working Hours, FCR |

**Table columns:**

| Column | Description |
|--------|-------------|
| Unit | Equipment code (e.g. E 071) |
| Liters | Today's fuel consumption |
| Working Hours | Total operating hours |
| FCR | Fuel Consumption Rate (liters/hour) |

**Filters:** Select site and date, then click **Terapkan** (Apply).

> The system automatically detects FCR anomalies (2σ outliers) and sends notifications to relevant users.

---

### 3.4 Equipment

**Path:** `Sidebar → Equipment` *(Admin)*

**Equipment Assignment** page for assigning heavy equipment from arkfleet-next to PITs at the active site.

#### Viewing Assignments

The table shows currently assigned equipment:

| Column | Description |
|--------|-------------|
| Unit Code | Equipment code (e.g. DZ 040) |
| Description | Equipment name/description |
| Plant Type | Equipment type (Excavator, Dump Truck, etc.) |
| Project | Project/site code |
| PIT | Assignment PIT |
| Tracking | Active status for fuel/deployment tracking |

#### Assigning Equipment

1. Click **Assign Equipment**.
2. A search drawer opens — type unit code or description.
3. Select equipment from search results (data from arkfleet-next API).
4. Select the target **PIT**.
5. Confirm — the equipment appears in the assignment table.

#### Removing an Assignment

Click the **Delete** (🗑) icon on the equipment row and confirm.

> Only assigned equipment appears in the Fuel and Deployment forms of daily entries.

---

### 3.5 Plan vs Actual

#### Monthly Plan

**Path:** `Sidebar → Monthly Plan`

Enter monthly production targets per PIT.

**Creating a new plan:**
1. Click **Plan Baru** (New Plan).
2. Select **Site**, **Year**, and **Month**.
3. Save — you are redirected to the target edit page.

**Entering targets per PIT:**

| Metric | Unit | Description |
|--------|------|-------------|
| OB | Bcm | Overburden removal target |
| Coal | Ton | Coal getting target |
| Stripping Ratio | — | SR target (optional) |

The grid shows all PITs at the site with target columns per metric and owner (Internal/Contractor).

1. Enter target values in the grid.
2. Click **Simpan Target** (Save Targets).

#### Achievement Calculation

Achievement % is automatically calculated by the backend **Calculation Engine**:

```
Achievement % = (Actual MTD / Target MTD) × 100
```

- **Actual** — aggregated from *approved* production data
- **Target** — from monthly plan per PIT
- Not stored as raw columns; always recalculated for consistency

#### Variance Analysis

**Path:** `Sidebar → Variance`

Plan vs actual variance analysis per PIT per month.

**Components:**

| Component | Description |
|-----------|-------------|
| **Variance Table** | Per-PIT: target, actual, variance, achievement % |
| **Plan vs Actual Chart** | Visual comparison chart |
| **Loss Contribution Chart** | Production loss from rain & slippery hours |

**Filters:** Site, Year, Month → click **Refresh**.

Rain/slippery hour data is sourced from the **Info Site** tab of daily entries.

---

### 3.6 CCR Hourly Production

Hourly **per-equipment** production monitoring for non-coal materials (Limestone, Shalestone). Replaces Google Sheets CCR at sites 021C and 025C.

#### Hourly Dashboard

**Path:** `Sidebar → CCR Hourly → CCR Dashboard`

| Component | Description |
|-----------|-------------|
| **KPI Card** | DTD & MTD actual vs plan, achievement %, hourly target |
| **Heatmap** | Hour × equipment grid, red→green based on achievement |
| **Trend Chart** | Total tonnage per hour |
| **Fleet Status** | Unit count per role (loader, hauler, grader) |

**Filters:**
- **Site** — 021C, 025C (CCR-enabled sites)
- **Date**
- **Material** — Limestone (LS), Shalestone (SH)

**Export:** **Export Excel** and **Export PDF** buttons in the toolbar.

#### Hourly Entry Grid

**Path:** `Sidebar → CCR Hourly → Hourly Entry`

Tonnage input grid per hour per equipment — equivalent to one cell in the CCR Google Sheet.

**Creating an hourly entry:**
1. Click **Entry Baru** (New Entry).
2. Select date and site.
3. Open the entry → grid displays:
   - **Rows** = hour intervals (00:00–01:00 through 23:00–24:00)
   - **Columns** = assigned equipment (E 084, E 096, etc.)
   - **Cells** = tonnage (Mton) for that hour

4. Enter tonnage per cell.
5. Select **Material** (Limestone / Shalestone) and **Shift**.
6. Save — follows the same draft → submit → approve workflow.

#### Material Types

| Material | Label | Sites |
|----------|-------|-------|
| `limestone` | Limestone (LS) | 021C, 025C |
| `shalestone` | Shalestone (SH) | 021C |
| `coal` | Coal | *(reserved)* |
| `other` | Other | — |

#### Per-hour Per-equipment Tracking

Each grid cell represents one unique record: `(date, site, equipment, material, hour)`. Heatmap colors on the dashboard are calculated from:

```
Hourly target = daily_plan_tonnage / operating_hours_per_day
Cell achievement = actual_tonnage / hourly_target
```

---

### 3.7 Procurement KPI

**Path:** `Sidebar → Procurement`

Procurement and material KPI dashboard from **ARK-GS** (SAP B1 sync, twice daily at 06:05 & 12:05 WITA). Data cached for 6 hours in Redis.

**Filters:** Site, Year, Month → **Terapkan** (Apply)

#### Budget Performance

| Metric | Description |
|--------|-------------|
| Budget Amount | Total plant budget |
| Actual Amount | Actual expenditure |
| Utilization % | Budget utilization percentage |

A separate **CAPEX Budget** card is also available.

#### GRPO Completion

| Metric | Description |
|--------|-------------|
| PO Amount | Purchase Order sent value |
| GRPO Amount | Goods Receipt value |
| Completion % | Percentage of PO received |
| Status | Green (≥80%) / Red (<80%) |

#### NPI Efficiency

| Metric | Description |
|--------|-------------|
| Incoming Qty | Goods received |
| Outgoing Qty | Material issued |
| NPI Index | Incoming / Outgoing — lower is more efficient |

**Additional charts:**
- PO vs GRPO comparison chart
- NPI In/Out chart

The **Last Synced** badge shows the most recent ARK-GS sync time.

---

### 3.8 Reports

**Path:** `Sidebar → Reports`

#### Daily Report (DPR)

Generate daily production report per site.

1. Select **Site** and **Date**.
2. Click **Download PDF** or **Download Excel**.
3. File downloads automatically.

#### Custom Period Report

**Path:** `Sidebar → Reports → Buat Laporan Custom`

1. Click **Buat Laporan Custom** (Create Custom Report).
2. Set date range (from–to).
3. Optional filters: site, PIT.
4. Choose PDF or Excel format.
5. Generate and download.

#### Consolidated Report

**Path:** `Sidebar → Reports → Buka Laporan Konsolidasi`

Multi-site and multi-period summary including:
- Production (OB, Coal)
- Fuel consumption
- Equipment deployment
- Site info (weather, manpower)

1. Select sites (multiple allowed).
2. Set the period.
3. Generate consolidated report.

---

### 3.9 Notifications

#### Notification Bell

**Location:** 🔔 icon in the top-right header.

- Red badge shows unread notification count.
- Click the icon to open the notifications page.
- Auto-refreshes every 60 seconds.

#### Notifications Page

**Path:** `Sidebar → Notifikasi`

| Action | Function |
|--------|----------|
| **Tandai dibaca** (Mark as read) | Mark a single notification as read |
| **Tandai Semua Dibaca** (Mark all read) | Mark all notifications as read |

Unread notifications are highlighted with a light green background.

#### Alert Types

| Type | Trigger | Schedule |
|------|---------|----------|
| **Achievement Below Target** | OB/Coal achievement < 90% | Every hour at :30 (07:00–18:00 WITA) |
| **Fuel Consumption Anomaly** | FCR outlier (z-score > 2σ) | Every hour at :30 (07:00–18:00 WITA) |
| **Input Data Reminder** | No entry for today | 20:00 WITA |
| **Daily Summary** | Production KPI summary | 07:00 WITA |

> If a Telegram bot is configured, notifications are also sent to the Telegram channel.

---

## 4. Master Data (Admin)

All master data menus require the **Admin** role.

### 4.1 Sites & PITs

**Sites** — `Sidebar → Sites`

| Field | Description |
|-------|-------------|
| Code | Site code (e.g. 022C) |
| Name | Project name |
| Active | Active/inactive status |

**PITs** — `Sidebar → PITs`

| Field | Description |
|-------|-------------|
| Code | PIT code (e.g. KF1, KF2) |
| Site | Parent site |
| Owner | Internal / Contractor |

### 4.2 Shifts

**Path:** `Sidebar → Shifts`

Manage operational shifts:

| Shift | Label |
|-------|-------|
| `day` | Siang (Day) |
| `night` | Malam (Night) |

### 4.3 Fuel Types & Prices

**Fuel Types** — `Sidebar → Jenis BBM`

| Field | Description |
|-------|-------------|
| Name | Type name (e.g. Industrial Diesel) |
| Code | Short code |
| Active | Status |

**Fuel Prices** — `Sidebar → Harga BBM`

| Field | Description |
|-------|-------------|
| Fuel Type | Fuel type reference |
| Price | Price per liter (IDR) |
| Effective From | Effective date |

### 4.4 Users & Roles

**Path:** `Sidebar → Pengguna`

| Field | Description |
|-------|-------------|
| Name | Full name |
| Email | Email address (login) |
| Username | Alternative username (optional) |
| Role | Admin / Supervisor / Management / Fuel Officer |
| Site | User's default site |

**Roles and permissions:**

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

See [§3.4 Equipment](#34-equipment) — only Admin can manage equipment-to-PIT assignments.

For CCR hourly, assignments also support additional fields:
- **Material Type** — limestone, shalestone
- **Equipment Role** — loader, hauler, grader
- **Display Order** — column order in the hourly grid

---

## 5. Mobile Usage

### 5.1 PWA Installation

See [§2.5 PWA Installation](#25-pwa-installation-mobile).

Once installed, the app runs fullscreen without the browser address bar — ideal for supervisors in the field.

### 5.2 Offline Mode

ARKA MineOps supports data entry without an internet connection.

**How it works:**
1. When offline, a red banner appears at the top: *"Mode offline — X entri dalam antrian"* (Offline mode — X entries in queue).
2. Create or edit daily entries as usual — data is saved in the browser's **IndexedDB**.
3. When connectivity returns, the banner turns yellow: *"Offline sync: X entri menunggu sinkronisasi"* (X entries awaiting sync).
4. Click the **Sync** button in the header (desktop) to send the queue to the server.
5. Sync is **idempotent** (UUID-based) — safe to retry.

> Sync entries before logging out or clearing browser data.

### 5.3 Site Selector on Mobile

On small screens, the Site Selector is in the **⋯** (three dots) menu in the top-right header:
1. Tap **⋯**.
2. Select a site from the dropdown inside the menu.
3. The active site updates immediately.

### 5.4 Navigation Drawer

On mobile (< 1024px), the sidebar is hidden and replaced by a drawer:

1. Tap the **☰** (hamburger) icon in the top-left header.
2. The menu drawer slides in from the left.
3. Tap a menu item — the drawer closes automatically.
4. Tap **✕** or outside the drawer to close.

**Mobile tips:**
- Input forms use numeric keyboards for number fields.
- Action buttons are sized for touch interaction.
- Use landscape mode for wide hourly grids.

---

## 6. Troubleshooting

### 6.1 Common Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| Cannot login | Wrong email/password | Verify credentials; contact Admin for reset |
| Blank page after login | Stale browser cache | Hard refresh (Ctrl+Shift+R) or clear cache |
| Dashboard data not updating | Redis cache / entry not approved | Ensure entry is approved; wait 60s for auto-refresh |
| Equipment missing from fuel form | Not assigned | Admin: assign equipment at `Sidebar → Equipment` |
| Duplicate entry rejected | Entry exists for date+site | Edit existing entry instead of creating new |
| Excel import failed | Wrong file format | Verify import type (DPR/Fuel/Site Info) and .xlsx format |
| Procurement data empty | ARK-GS not synced | Check Last Synced badge; data syncs at 06:05 & 12:05 WITA |
| Offline sync failed | Connection lost during sync | Ensure online, click Sync again |
| Master Data menu missing | Not Admin role | Contact Admin for role change |
| CCR heatmap empty | No hourly data / plan | Enter hourly data and set material daily plan |

### 6.2 Common Errors

| Error | Meaning | Action |
|-------|---------|--------|
| 403 Forbidden | Insufficient access | Ask Admin to adjust role/permissions |
| 419 Page Expired | Session expired | Log in again |
| 422 Validation Error | Invalid data | Check fields marked in red |
| 500 Server Error | Server error | Retry; contact support if persistent |

### 6.3 Contact Support

| Channel | Details |
|---------|---------|
| **PT. Arkananta IT Support** | Contact internal IT team |
| **Application URL** | https://vps-iwan1.tail8334ce.ts.net |
| **Demo login** | admin@mineops.test / password |

When reporting issues, include:
1. URL of the affected page
2. Selected site and date
3. Your account role
4. Screenshot of the error (if any)
5. Browser and device used

---

*This document is the official ARKA MineOps user guide. For technical architecture details, see `docs/architecture.md` and `docs/concept.md`.*
