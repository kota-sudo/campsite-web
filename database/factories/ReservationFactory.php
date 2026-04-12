<?php

namespace Database\Factories;

use App\Models\Campsite;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $checkIn  = fake()->dateTimeBetween('-1 month', '+2 months');
        $nights   = fake()->numberBetween(1, 5);
        $checkOut = (clone $checkIn)->modify("+{$nights} days");

        $campsite   = Campsite::inRandomOrder()->first() ?? Campsite::factory()->create();
        $totalPrice = $campsite->price_per_night * $nights;

        return [
            'user_id'        => User::factory(),
            'campsite_id'    => $campsite->id,
            'check_in_date'  => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
            'num_guests'     => fake()->numberBetween(1, $campsite->capacity),
            'total_price'    => $totalPrice,
            'status'         => fake()->randomElement(['pending', 'confirmed', 'confirmed', 'cancelled']),
            'notes'          => fake()->boolean(30) ? fake()->sentence() : null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(['status' => 'confirmed']);
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled']);
    }
}
