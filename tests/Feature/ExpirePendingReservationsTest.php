<?php

namespace Tests\Feature;

use App\Mail\ReservationExpired;
use App\Models\Campsite;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ExpirePendingReservationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_reservation_older_than_48h_is_cancelled(): void
    {
        Mail::fake();

        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true]);

        $reservation = Reservation::factory()->create([
            'user_id'     => $user->id,
            'campsite_id' => $campsite->id,
            'status'      => 'pending',
            'check_in_date' => today()->addDays(10)->toDateString(),
        ]);

        // created_at を49時間前に強制セット
        $reservation->forceFill(['created_at' => now()->subHours(49)])->save();

        $this->artisan('reservations:expire-pending')->assertSuccessful();

        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_expired_mail_is_sent_on_auto_cancel(): void
    {
        Mail::fake();

        $user     = User::factory()->create(['email' => 'guest@example.com']);
        $campsite = Campsite::factory()->create(['is_active' => true]);

        $reservation = Reservation::factory()->create([
            'user_id'        => $user->id,
            'campsite_id'    => $campsite->id,
            'status'         => 'pending',
            'check_in_date'  => today()->addDays(10)->toDateString(),
            'check_out_date' => today()->addDays(12)->toDateString(),
        ]);

        $reservation->forceFill(['created_at' => now()->subHours(49)])->save();

        $this->artisan('reservations:expire-pending')->assertSuccessful();

        Mail::assertSent(ReservationExpired::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_recent_pending_reservation_is_not_cancelled(): void
    {
        Mail::fake();

        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true]);

        // 作成1時間後 → まだ期限内
        $reservation = Reservation::factory()->create([
            'user_id'     => $user->id,
            'campsite_id' => $campsite->id,
            'status'      => 'pending',
        ]);

        $reservation->forceFill(['created_at' => now()->subHour()])->save();

        $this->artisan('reservations:expire-pending')->assertSuccessful();

        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'pending',
        ]);

        Mail::assertNothingSent();
    }

    public function test_confirmed_reservation_is_not_affected(): void
    {
        Mail::fake();

        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true]);

        // confirmed は対象外
        $reservation = Reservation::factory()->create([
            'user_id'     => $user->id,
            'campsite_id' => $campsite->id,
            'status'      => 'confirmed',
        ]);

        $reservation->forceFill(['created_at' => now()->subHours(100)])->save();

        $this->artisan('reservations:expire-pending')->assertSuccessful();

        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'confirmed',
        ]);

        Mail::assertNothingSent();
    }

    public function test_multiple_expired_pending_reservations_are_all_cancelled(): void
    {
        Mail::fake();

        $campsite = Campsite::factory()->create(['is_active' => true]);

        $reservations = Reservation::factory()->count(3)->create([
            'campsite_id' => $campsite->id,
            'status'      => 'pending',
        ]);

        foreach ($reservations as $r) {
            $r->forceFill(['created_at' => now()->subHours(50)])->save();
        }

        $this->artisan('reservations:expire-pending')->assertSuccessful();

        foreach ($reservations as $r) {
            $this->assertDatabaseHas('reservations', ['id' => $r->id, 'status' => 'cancelled']);
        }

        Mail::assertSentCount(3);
    }
}
