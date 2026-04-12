<?php

namespace Tests\Feature;

use App\Models\Campsite;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_campsite_index_is_accessible_without_login(): void
    {
        Campsite::factory()->count(3)->create(['is_active' => true]);

        $response = $this->get(route('campsites.index'));

        $response->assertOk();
    }

    public function test_inactive_campsites_are_not_shown(): void
    {
        $active   = Campsite::factory()->create(['is_active' => true, 'name' => 'アクティブサイト']);
        $inactive = Campsite::factory()->create(['is_active' => false, 'name' => '非アクティブサイト']);

        $response = $this->get(route('campsites.index'));

        $response->assertSee($active->name);
        $response->assertDontSee($inactive->name);
    }

    public function test_campsite_index_filters_by_type(): void
    {
        $tent     = Campsite::factory()->create(['is_active' => true, 'type' => 'tent', 'name' => 'テントサイト']);
        $glamping = Campsite::factory()->create(['is_active' => true, 'type' => 'glamping', 'name' => 'グランピングサイト']);

        $response = $this->get(route('campsites.index', ['type' => 'tent']));

        $response->assertSee($tent->name);
        $response->assertDontSee($glamping->name);
    }

    public function test_campsite_index_filters_by_guest_count(): void
    {
        $small = Campsite::factory()->create(['is_active' => true, 'capacity' => 2, 'name' => '小サイト']);
        $large = Campsite::factory()->create(['is_active' => true, 'capacity' => 8, 'name' => '大サイト']);

        $response = $this->get(route('campsites.index', ['guests' => 5]));

        $response->assertDontSee($small->name);
        $response->assertSee($large->name);
    }

    public function test_campsite_show_is_accessible_without_login(): void
    {
        $campsite = Campsite::factory()->create(['is_active' => true]);

        $response = $this->get(route('campsites.show', $campsite));

        $response->assertOk();
        $response->assertSee($campsite->name);
    }

    public function test_campsite_show_displays_availability_when_dates_provided(): void
    {
        $campsite = Campsite::factory()->create(['is_active' => true]);

        $checkIn  = now()->addDays(10)->toDateString();
        $checkOut = now()->addDays(12)->toDateString();

        $response = $this->get(route('campsites.show', [
            'campsite'  => $campsite,
            'check_in'  => $checkIn,
            'check_out' => $checkOut,
        ]));

        $response->assertOk();
        $response->assertSee('空きあり');
    }

    public function test_campsite_show_displays_unavailable_when_booked(): void
    {
        $campsite = Campsite::factory()->create(['is_active' => true]);

        $checkIn  = now()->addDays(10)->toDateString();
        $checkOut = now()->addDays(12)->toDateString();

        // 重複する予約を作成
        Reservation::factory()->create([
            'campsite_id'    => $campsite->id,
            'check_in_date'  => $checkIn,
            'check_out_date' => $checkOut,
            'status'         => 'confirmed',
        ]);

        $response = $this->get(route('campsites.show', [
            'campsite'  => $campsite,
            'check_in'  => $checkIn,
            'check_out' => $checkOut,
        ]));

        $response->assertOk();
        $response->assertSee('満室');
    }

    public function test_campsite_index_excludes_booked_sites_when_dates_provided(): void
    {
        $booked    = Campsite::factory()->create(['is_active' => true, 'name' => '予約済みサイト']);
        $available = Campsite::factory()->create(['is_active' => true, 'name' => '空きサイト']);

        $checkIn  = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(7)->toDateString();

        Reservation::factory()->create([
            'campsite_id'    => $booked->id,
            'check_in_date'  => $checkIn,
            'check_out_date' => $checkOut,
            'status'         => 'confirmed',
        ]);

        $response = $this->get(route('campsites.index', [
            'check_in'  => $checkIn,
            'check_out' => $checkOut,
        ]));

        $response->assertDontSee($booked->name);
        $response->assertSee($available->name);
    }
}
