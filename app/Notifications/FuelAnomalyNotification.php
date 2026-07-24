<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class FuelAnomalyNotification extends Notification
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
            'title' => 'Anomali Fuel Consumption',
            'message' => "Unit {$this->data['unit_code']}: FCR {$this->data['fcr']} (z-score: {$this->data['z_score']})",
            'equipment_id' => $this->data['equipment_id'],
        ];
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->content("⛽ Anomali FCR\nUnit {$this->data['unit_code']}: FCR {$this->data['fcr']}");
    }
}
