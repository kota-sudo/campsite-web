<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['name' => 'トイレ',     'icon' => 'toilet'],
            ['name' => 'シャワー',   'icon' => 'shower'],
            ['name' => '炊事場',     'icon' => 'sink'],
            ['name' => '電源',       'icon' => 'plug'],
            ['name' => 'Wi-Fi',      'icon' => 'wifi'],
            ['name' => 'BBQコンロ', 'icon' => 'bbq'],
            ['name' => '焚き火台',   'icon' => 'fire'],
            ['name' => '駐車場',     'icon' => 'parking'],
            ['name' => 'ゴミ捨て場', 'icon' => 'trash'],
            ['name' => '売店',       'icon' => 'shop'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::firstOrCreate(['name' => $amenity['name']], $amenity);
        }
    }
}
