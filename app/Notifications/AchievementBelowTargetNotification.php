<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class AchievementBelowTargetNotification extends Notification
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
            'title' => 'Achievement Di Bawah Target',
            'message' => "Site {$this->data['site_code']}: {$this->data['metric']} achievement {$this->data['achievement']}% (target 90%)",
            'site_id' => $this->data['site_id'] ?? null,
            'achievement' => $this->data['achievement'],
        ];
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->content("⚠️ Achievement Di Bawah Target\n{$this->data['site_code']}: {$this->data['metric']} = {$this->data['achievement']}%");
    }
}
