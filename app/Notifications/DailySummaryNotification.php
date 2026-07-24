<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class DailySummaryNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $summary
     */
    public function __construct(
        public array $summary,
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
            'title' => 'Ringkasan Harian',
            'message' => $this->formatMessage(),
            'summary' => $this->summary,
        ];
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        return TelegramMessage::create()->content($this->formatMessage());
    }

    protected function formatMessage(): string
    {
        $s = $this->summary;

        return "📊 Ringkasan Harian {$s['date']}\n".
            "Site: {$s['site_code']}\n".
            'OB: '.number_format($s['ob'], 0, ',', '.')." Bcm\n".
            'Coal: '.number_format($s['coal'], 0, ',', '.')." Ton\n".
            'SR: '.($s['sr'] ?? '-')."\n".
            'Fuel: '.number_format($s['fuel'], 0, ',', '.').' L';
    }
}
