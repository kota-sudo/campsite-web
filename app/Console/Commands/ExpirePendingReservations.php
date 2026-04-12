<?php

namespace App\Console\Commands;

use App\Mail\ReservationExpired;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ExpirePendingReservations extends Command
{
    protected $signature   = 'reservations:expire-pending';
    protected $description = '48時間以上承認されていないpending予約を自動キャンセルする';

    public function handle(): int
    {
        $expireAt = now()->subHours(48);

        $reservations = Reservation::with(['user', 'campsite'])
            ->where('status', 'pending')
            ->where('created_at', '<=', $expireAt)
            ->get();

        $count = 0;
        foreach ($reservations as $reservation) {
            $reservation->update(['status' => 'cancelled']);

            Mail::to($reservation->user->email)
                ->send(new ReservationExpired($reservation));

            $count++;
        }

        $this->info("{$count} 件の期限切れpending予約をキャンセルしました");

        return self::SUCCESS;
    }
}
