<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Campsite;
use Illuminate\Database\Seeder;

class BulkCampsiteSeeder extends Seeder
{
    public function run(): void
    {
        $amenityIds = Amenity::pluck('id')->toArray();

        Campsite::factory()
            ->count(994)
            ->create()
            ->each(function ($campsite) use ($amenityIds) {
                if (!empty($amenityIds)) {
                    $count = rand(2, min(6, count($amenityIds)));
                    $randomIds = collect($amenityIds)->shuffle()->take($count)->toArray();
                    $campsite->amenities()->sync($randomIds);
                }
            });
    }
}