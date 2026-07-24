<?php

namespace App\Console\Commands;

use App\Models\DailyEntry;
use App\Models\Site;
use App\Models\User;
use App\Notifications\EntryReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendEntryReminderCommand extends Command
{
    protected $signature = 'mineops:send-entry-reminder';

    protected $description = 'Remind supervisors if no entry by deadline';

    public function handle(): int
    {
        $date = Carbon::today();
        $supervisors = User::role('supervisor')->get();

        foreach (Site::where('is_active', true)->get() as $site) {
            $hasEntry = DailyEntry::query()
                ->where('site_id', $site->id)
                ->whereDate('production_date', $date)
                ->exists();

            if (! $hasEntry) {
                foreach ($supervisors as $user) {
                    $user->notify(new EntryReminderNotification([
                        'site_id' => $site->id,
                        'site_code' => $site->code,
                        'date' => $date->toDateString(),
                    ]));
                }
            }
        }

        $this->info('Entry reminders sent.');

        return self::SUCCESS;
    }
}
