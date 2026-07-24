<?php

namespace App\Console\Commands;

use App\Services\CalculationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ReconcileMtdCommand extends Command
{
    protected $signature = 'mineops:reconcile-mtd {site_id} {--ob=} {--coal=}';

    protected $description = 'Compare MTD system values vs Excel summary';

    public function handle(CalculationService $calculationService): int
    {
        $siteId = (int) $this->argument('site_id');
        $date = Carbon::today();

        $systemOb = $calculationService->mtd($siteId, $date, 'ob_removal_bcm');
        $systemCoal = $calculationService->mtd($siteId, $date, 'coal_getting_ton');

        $excelOb = (float) $this->option('ob');
        $excelCoal = (float) $this->option('coal');

        $obDiff = $excelOb > 0 ? abs($systemOb - $excelOb) / $excelOb * 100 : 0;
        $coalDiff = $excelCoal > 0 ? abs($systemCoal - $excelCoal) / $excelCoal * 100 : 0;

        $this->table(['Metric', 'System', 'Excel', 'Diff %'], [
            ['OB (Bcm)', $systemOb, $excelOb, round($obDiff, 2)],
            ['Coal (Ton)', $systemCoal, $excelCoal, round($coalDiff, 2)],
        ]);

        $tolerance = 0.5;
        if ($obDiff > $tolerance || $coalDiff > $tolerance) {
            $this->warn("Selisih melebihi toleransi {$tolerance}%");

            return self::FAILURE;
        }

        $this->info('Rekonsiliasi OK.');

        return self::SUCCESS;
    }
}
