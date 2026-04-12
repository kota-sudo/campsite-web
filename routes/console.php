<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// チェックイン3日前リマインダー (毎朝9時)
Schedule::command('reservations:send-reminders')->dailyAt('09:00');

// 48時間未承認のpending予約を自動キャンセル (毎時)
Schedule::command('reservations:expire-pending')->hourly();
