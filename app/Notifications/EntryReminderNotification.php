<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class EntryReminderNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public array $data,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if (config('services.telegram.bot_token')) {
            $channels[] = 'telegram';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Reminder Input Data',
            'message' => "Site {$this->data['site_code']}: belum ada entry untuk {$this->data['date']}",
            'site_id' => $this->data['site_id'],
        ];
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->content("📝 Reminder: Site {$this->data['site_code']} belum input data {$this->data['date']}");
    }
}
