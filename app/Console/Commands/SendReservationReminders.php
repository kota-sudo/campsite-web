<?php

namespace App\Console\Commands;

use App\Mail\ReservationReminder;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReservationReminders extends Command
{
    protected $signature   = 'reservations:send-reminders';
    protected $description = 'チェックイン3日前のゲストにリマインダーメールを送信する';

    public function handle(): int
    {
        $targetDate = today()->addDays(3)->toDateString();

        $reservations = Reservation::with(['user', 'campsite'])
            ->where('status', 'confirmed')
            ->where('check_in_date', $targetDate)
            ->get();

        $count = 0;
        foreach ($reservations as $reservation) {
            Mail::to($reservation->user->email)
                ->send(new ReservationReminder($reservation));
            $count++;
        }

        $this->info("リマインダーを {$count} 件送信しました（対象日: {$targetDate}）");

        return self::SUCCESS;
    }
}
