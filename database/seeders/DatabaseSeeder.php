<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 固定テストユーザー
        User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 追加ユーザー 9名
        User::factory(9)->create();

        // 設備マスタ → 固定サイト → 追加サイト → 予約 の順に投入
        $this->call([
            AmenitySeeder::class,
            CampsiteSeeder::class,
            BulkCampsiteSeeder::class,
            ReservationSeeder::class,
        ]);
    }
}