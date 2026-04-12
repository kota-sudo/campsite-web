<?php

namespace Tests\Unit;

use App\Models\Campsite;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationModelTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // isCancellable()
    // ---------------------------------------------------------------

    public function test_is_cancellable_returns_true_for_future_confirmed(): void
    {
        $reservation = Reservation::factory()->create([
            'status'        => 'confirmed',
            'check_in_date' => today()->addDays(5)->toDateString(),
        ]);

        $this->assertTrue($reservation->isCancellable());
    }

    public function test_is_cancellable_returns_true_for_future_pending(): void
    {
        $reservation = Reservation::factory()->create([
            'status'        => 'pending',
            'check_in_date' => today()->addDays(2)->toDateString(),
        ]);

        $this->assertTrue($reservation->isCancellable());
    }

    public function test_is_cancellable_returns_false_for_today_checkin(): void
    {
        // 当日チェックインはキャンセル不可（gt(today()) が false）
        $reservation = Reservation::factory()->create([
            'status'        => 'confirmed',
            'check_in_date' => today()->toDateString(),
        ]);

        $this->assertFalse($reservation->isCancellable());
    }

    public function test_is_cancellable_returns_false_for_past_checkin(): void
    {
        $reservation = Reservation::factory()->create([
            'status'        => 'confirmed',
            'check_in_date' => today()->subDays(3)->toDateString(),
        ]);

        $this->assertFalse($reservation->isCancellable());
    }

    public function test_is_cancellable_returns_false_for_cancelled_status(): void
    {
        $reservation = Reservation::factory()->create([
            'status'        => 'cancelled',
            'check_in_date' => today()->addDays(5)->toDateString(),
        ]);

        $this->assertFalse($reservation->isCancellable());
    }

    // ---------------------------------------------------------------
    // cancellationDeadline()
    // ---------------------------------------------------------------

    public function test_cancellation_deadline_is_day_before_checkin(): void
    {
        $checkIn = today()->addDays(7);
        $reservation = Reservation::factory()->create([
            'check_in_date' => $checkIn->toDateString(),
        ]);

        $deadline = $reservation->cancellationDeadline();

        $this->assertTrue($deadline->isSameDay($checkIn->subDay()));
    }

    // ---------------------------------------------------------------
    // nights()
    // ---------------------------------------------------------------

    public function test_nights_returns_correct_count(): void
    {
        $reservation = Reservation::factory()->create([
            'check_in_date'  => '2026-05-01',
            'check_out_date' => '2026-05-04',
        ]);

        $this->assertSame(3, $reservation->nights());
    }

    public function test_nights_returns_1_for_single_night(): void
    {
        $reservation = Reservation::factory()->create([
            'check_in_date'  => '2026-06-10',
            'check_out_date' => '2026-06-11',
        ]);

        $this->assertSame(1, $reservation->nights());
    }
}
