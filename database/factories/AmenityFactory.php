<?php

namespace Database\Factories;

use App\Models\Amenity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Amenity>
 */
class AmenityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'トイレ', 'シャワー', '炊事場', '電源', 'Wi-Fi',
                'BBQコンロ', '焚き火台', '駐車場', 'ゴミ捨て場', '売店',
            ]),
            'icon' => null,
        ];
    }
}
