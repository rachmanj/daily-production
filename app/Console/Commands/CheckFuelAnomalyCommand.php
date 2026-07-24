<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Models\User;
use App\Notifications\FuelAnomalyNotification;
use App\Services\AnomalyDetectionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckFuelAnomalyCommand extends Command
{
    protected $signature = 'mineops:check-fuel-anomaly';

    protected $description = 'Detect FCR outliers and send notifications';

    public function handle(AnomalyDetectionService $anomalyService): int
    {
        $date = Carbon::today();
        $users = User::role('admin')->get();

        foreach (Site::where('is_active', true)->get() as $site) {
            $outliers = $anomalyService->detectFcrOutliers($site->id, $date);

            foreach ($outliers as $outlier) {
                foreach ($users as $user) {
                    $user->notify(new FuelAnomalyNotification($outlier));
                }
            }
        }

        $this->info('Fuel anomaly check completed.');

        return self::SUCCESS;
    }
}
