<?php

namespace Tests\Feature;

use App\Mail\ReservationReminder;
use App\Models\Campsite;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendReservationRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_sent_to_guests_checking_in_in_3_days(): void
    {
        Mail::fake();

        $user     = User::factory()->create(['email' => 'guest@example.com']);
        $campsite = Campsite::factory()->create(['is_active' => true]);

        Reservation::factory()->create([
            'user_id'        => $user->id,
            'campsite_id'    => $campsite->id,
            'status'         => 'confirmed',
            'check_in_date'  => today()->addDays(3)->toDateString(),
            'check_out_date' => today()->addDays(5)->toDateString(),
        ]);

        $this->artisan('reservations:send-reminders')->assertSuccessful();

        Mail::assertSent(ReservationReminder::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_reminder_not_sent_for_non_3_day_checkins(): void
    {
        Mail::fake();

        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true]);

        // チェックイン5日後 → 対象外
        Reservation::factory()->create([
            'user_id'        => $user->id,
            'campsite_id'    => $campsite->id,
            'status'         => 'confirmed',
            'check_in_date'  => today()->addDays(5)->toDateString(),
            'check_out_date' => today()->addDays(7)->toDateString(),
        ]);

        $this->artisan('reservations:send-reminders')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_reminder_not_sent_for_pending_reservations(): void
    {
        Mail::fake();

        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true]);

        // pending は対象外（まだ承認されていない）
        Reservation::factory()->create([
            'user_id'        => $user->id,
            'campsite_id'    => $campsite->id,
            'status'         => 'pending',
            'check_in_date'  => today()->addDays(3)->toDateString(),
            'check_out_date' => today()->addDays(5)->toDateString(),
        ]);

        $this->artisan('reservations:send-reminders')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_reminder_not_sent_for_cancelled_reservations(): void
    {
        Mail::fake();

        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true]);

        Reservation::factory()->create([
            'user_id'        => $user->id,
            'campsite_id'    => $campsite->id,
            'status'         => 'cancelled',
            'check_in_date'  => today()->addDays(3)->toDateString(),
            'check_out_date' => today()->addDays(5)->toDateString(),
        ]);

        $this->artisan('reservations:send-reminders')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_multiple_reminders_sent_when_multiple_guests_check_in_same_day(): void
    {
        Mail::fake();

        $campsite = Campsite::factory()->create(['is_active' => true]);

        foreach (range(1, 3) as $i) {
            $user = User::factory()->create();
            Reservation::factory()->create([
                'user_id'        => $user->id,
                'campsite_id'    => $campsite->id,
                'status'         => 'confirmed',
                'check_in_date'  => today()->addDays(3)->toDateString(),
                'check_out_date' => today()->addDays(4)->toDateString(),
            ]);
        }

        $this->artisan('reservations:send-reminders')->assertSuccessful();

        Mail::assertSentCount(3);
    }
}
