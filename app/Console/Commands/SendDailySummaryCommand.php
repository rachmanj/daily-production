<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Models\User;
use App\Notifications\DailySummaryNotification;
use App\Services\CalculationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDailySummaryCommand extends Command
{
    protected $signature = 'mineops:send-daily-summary';

    protected $description = 'Send daily production summary via Telegram';

    public function handle(CalculationService $calculationService): int
    {
        $date = Carbon::yesterday();
        $users = User::role(['management', 'admin'])->get();

        foreach (Site::where('is_active', true)->get() as $site) {
            $summary = [
                'date' => $date->toDateString(),
                'site_code' => $site->code,
                'ob' => $calculationService->dailyValue($site->id, $date, 'ob_removal_bcm'),
                'coal' => $calculationService->dailyValue($site->id, $date, 'coal_getting_ton'),
                'sr' => $calculationService->siteStrippingRatio($site->id, $date),
                'fuel' => $calculationService->totalFuelLiters($site->id, $date),
            ];

            foreach ($users as $user) {
                $user->notify(new DailySummaryNotification($summary));
            }
        }

        $this->info('Daily summary sent.');

        return self::SUCCESS;
    }
}
