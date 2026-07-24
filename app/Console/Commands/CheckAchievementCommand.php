<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Models\User;
use App\Notifications\AchievementBelowTargetNotification;
use App\Services\CalculationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckAchievementCommand extends Command
{
    protected $signature = 'mineops:check-achievement';

    protected $description = 'Check achievement below target and send notifications';

    public function handle(CalculationService $calculationService): int
    {
        $date = Carbon::today();
        $users = User::role('management')->get();

        foreach (Site::where('is_active', true)->get() as $site) {
            foreach (['ob_removal_bcm' => 'OB', 'coal_getting_ton' => 'Coal'] as $metric => $label) {
                $achievement = $calculationService->achievementForSite($site->id, $date, $metric);

                if ($achievement !== null && $achievement < 90) {
                    foreach ($users as $user) {
                        $user->notify(new AchievementBelowTargetNotification([
                            'site_id' => $site->id,
                            'site_code' => $site->code,
                            'metric' => $label,
                            'achievement' => $achievement,
                        ]));
                    }
                }
            }
        }

        $this->info('Achievement check completed.');

        return self::SUCCESS;
    }
}
