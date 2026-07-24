<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('mineops:send-daily-summary')->dailyAt('07:00')->timezone('Asia/Makassar');
Schedule::command('mineops:send-entry-reminder')->dailyAt('20:00')->timezone('Asia/Makassar');
Schedule::command('mineops:check-achievement')->hourlyAt(30)->between('7:00', '18:00')->timezone('Asia/Makassar');
Schedule::command('mineops:check-fuel-anomaly')->hourlyAt(30)->between('7:00', '18:00')->timezone('Asia/Makassar');
