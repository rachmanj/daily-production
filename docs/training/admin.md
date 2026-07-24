# Training — Admin

## Master Data

- Sites, PITs, Shifts, Jenis BBM, Harga BBM
- Users & Roles (Spatie Permission)
- Equipment Assignment (sync dari arkfleet-next)

## Excel Import

1. **Excel Imports → Upload** file DPR / Daily Info / Fuel Report
2. Preview hasil parse → Confirm untuk commit ke database

## Scheduled Tasks

Pastikan cron `* * * * * php artisan schedule:run` aktif di production.

Commands:
- `mineops:send-daily-summary`
- `mineops:send-entry-reminder`
- `mineops:check-achievement`
- `mineops:check-fuel-anomaly`
- `mineops:import-historical {directory}`
- `mineops:reconcile-mtd {site_id} --ob= --coal=`

## Go-Live Checklist

- [ ] Backup database sebelum import historis
- [ ] Queue worker (Horizon) running
- [ ] Redis cache aktif
- [ ] Env: ARKFLEET_*, ARKGS_*, TELEGRAM_*, OPENROUTER_*
- [ ] `php artisan migrate --force`
- [ ] `npm run build` di production
