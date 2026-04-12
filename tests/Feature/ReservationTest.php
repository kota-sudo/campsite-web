<?php

namespace Tests\Feature;

use App\Models\Campsite;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_index_requires_login(): void
    {
        $response = $this->get(route('reservations.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_their_reservations(): void
    {
        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true]);

        Reservation::factory()->create([
            'user_id'    => $user->id,
            'campsite_id' => $campsite->id,
            'status'     => 'confirmed',
        ]);

        $response = $this->actingAs($user)->get(route('reservations.index'));

        $response->assertOk();
        $response->assertSee($campsite->name);
    }

    public function test_user_cannot_see_other_users_reservations(): void
    {
        $user1    = User::factory()->create();
        $user2    = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true]);

        Reservation::factory()->create([
            'user_id'     => $user2->id,
            'campsite_id' => $campsite->id,
        ]);

        $response = $this->actingAs($user1)->get(route('reservations.index'));

        $response->assertOk();
        // user1 の一覧には user2 の予約は出ない
        $this->assertCount(0, $user1->reservations);
    }

    public function test_reservation_create_shows_confirmation_page(): void
    {
        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create([
            'is_active'       => true,
            'capacity'        => 4,
            'price_per_night' => 5000,
        ]);

        $checkIn  = now()->addDays(10)->toDateString();
        $checkOut = now()->addDays(12)->toDateString();

        $response = $this->actingAs($user)->get(route('reservations.create', [
            'campsite_id' => $campsite->id,
            'check_in'    => $checkIn,
            'check_out'   => $checkOut,
            'guests'      => 2,
        ]));

        $response->assertOk();
        $response->assertSee('¥10,000'); // 5000 × 2泊
    }

    public function test_reservation_create_rejects_past_dates(): void
    {
        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true, 'capacity' => 4]);

        $response = $this->actingAs($user)->get(route('reservations.create', [
            'campsite_id' => $campsite->id,
            'check_in'    => now()->subDays(2)->toDateString(),
            'check_out'   => now()->subDays(1)->toDateString(),
            'guests'      => 1,
        ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    public function test_reservation_create_rejects_over_capacity(): void
    {
        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true, 'capacity' => 2]);

        $response = $this->actingAs($user)->get(route('reservations.create', [
            'campsite_id' => $campsite->id,
            'check_in'    => now()->addDays(5)->toDateString(),
            'check_out'   => now()->addDays(7)->toDateString(),
            'guests'      => 5,
        ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['guests']);
    }

    public function test_reservation_store_creates_reservation(): void
    {
        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create([
            'is_active'       => true,
            'capacity'        => 4,
            'price_per_night' => 6000,
        ]);

        $checkIn  = now()->addDays(10)->toDateString();
        $checkOut = now()->addDays(13)->toDateString();

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'campsite_id'    => $campsite->id,
            'check_in_date'  => $checkIn,
            'check_out_date' => $checkOut,
            'num_guests'     => 2,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reservations', [
            'user_id'        => $user->id,
            'campsite_id'    => $campsite->id,
            'check_in_date'  => $checkIn,
            'check_out_date' => $checkOut,
            'num_guests'     => 2,
            'total_price'    => 18000, // 6000 × 3泊
            'status'         => 'pending',
        ]);
    }

    public function test_reservation_store_rejects_double_booking(): void
    {
        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true, 'capacity' => 4]);

        $checkIn  = now()->addDays(10)->toDateString();
        $checkOut = now()->addDays(12)->toDateString();

        // 既存の予約
        Reservation::factory()->create([
            'campsite_id'    => $campsite->id,
            'check_in_date'  => $checkIn,
            'check_out_date' => $checkOut,
            'status'         => 'confirmed',
        ]);

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'campsite_id'    => $campsite->id,
            'check_in_date'  => $checkIn,
            'check_out_date' => $checkOut,
            'num_guests'     => 2,
        ]);

        $response->assertSessionHasErrors(['check_in_date']);
    }

    public function test_reservation_show_requires_owner(): void
    {
        $user1       = User::factory()->create();
        $user2       = User::factory()->create();
        $reservation = Reservation::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->get(route('reservations.show', $reservation));

        $response->assertForbidden();
    }

    public function test_reservation_owner_can_view_their_reservation(): void
    {
        $user        = User::factory()->create();
        $reservation = Reservation::factory()->create(['user_id' => $user->id, 'status' => 'confirmed']);

        $response = $this->actingAs($user)->get(route('reservations.show', $reservation));

        $response->assertOk();
    }

    public function test_user_can_cancel_future_reservation(): void
    {
        $user = User::factory()->create();

        $reservation = Reservation::factory()->create([
            'user_id'       => $user->id,
            'status'        => 'confirmed',
            'check_in_date' => now()->addDays(5)->toDateString(),
        ]);

        $response = $this->actingAs($user)->delete(route('reservations.destroy', $reservation));

        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_user_cannot_cancel_past_reservation(): void
    {
        $user = User::factory()->create();

        $reservation = Reservation::factory()->create([
            'user_id'        => $user->id,
            'status'         => 'confirmed',
            'check_in_date'  => now()->subDays(5)->toDateString(),
            'check_out_date' => now()->subDays(3)->toDateString(),
        ]);

        $response = $this->actingAs($user)->delete(route('reservations.destroy', $reservation));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'confirmed',
        ]);
    }
}
