<?php

namespace Database\Seeders;

use App\Models\Campsite;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $users     = User::all();
        $campsites = Campsite::all();

        foreach ($users as $user) {
            $count = rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                $campsite = $campsites->random();
                $checkIn  = now()->addDays(rand(-30, 60));
                $nights   = rand(1, 4);
                $checkOut = $checkIn->copy()->addDays($nights);

                Reservation::create([
                    'user_id'        => $user->id,
                    'campsite_id'    => $campsite->id,
                    'check_in_date'  => $checkIn->toDateString(),
                    'check_out_date' => $checkOut->toDateString(),
                    'num_guests'     => rand(1, $campsite->capacity),
                    'total_price'    => $campsite->price_per_night * $nights,
                    'status'         => fake()->randomElement(['pending', 'confirmed', 'confirmed', 'cancelled']),
                    'notes'          => fake()->boolean(20) ? fake()->sentence() : null,
                ]);
            }
        }
    }
}
