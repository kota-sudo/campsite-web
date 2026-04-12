<?php

namespace Database\Factories;

use App\Models\Campsite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campsite>
 */
class CampsiteFactory extends Factory
{
    private static array $siteNames = [
        'tent'     => ['森のテントサイトA', '川沿いテントサイトB', '山麓テントサイトC', '星空テントサイトD'],
        'auto'     => ['オートキャンプサイト East', 'オートキャンプサイト West', 'オートキャンプサイト North'],
        'bungalow' => ['バンガロー「杉」', 'バンガロー「檜」', 'バンガロー「松」'],
        'glamping' => ['グランピングドーム α', 'グランピングドーム β'],
    ];

    public function definition(): array
    {
        $type = fake()->randomElement(['tent', 'auto', 'bungalow', 'glamping']);
        $names = self::$siteNames[$type];

        $capacityMap = ['tent' => [2, 6], 'auto' => [2, 8], 'bungalow' => [4, 8], 'glamping' => [2, 4]];
        [$minCap, $maxCap] = $capacityMap[$type];

        $priceMap = ['tent' => [3000, 6000], 'auto' => [4000, 8000], 'bungalow' => [8000, 15000], 'glamping' => [20000, 40000]];
        [$minPrice, $maxPrice] = $priceMap[$type];

        return [
            'name' => fake()->randomElement($names) . ' (' . fake()->unique()->numberBetween(1, 99999) . ')',
            'description'     => fake()->realTextBetween(80, 200),
            'type'            => $type,
            'capacity'        => fake()->numberBetween($minCap, $maxCap),
            'price_per_night' => fake()->numberBetween($minPrice / 1000, $maxPrice / 1000) * 1000,
            'is_active'       => fake()->boolean(90),
        ];
    }

    public function tent(): static
    {
        return $this->state(['type' => 'tent']);
    }

    public function glamping(): static
    {
        return $this->state([
            'type'            => 'glamping',
            'price_per_night' => fake()->numberBetween(20, 40) * 1000,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
