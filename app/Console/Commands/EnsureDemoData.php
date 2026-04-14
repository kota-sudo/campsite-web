<?php

namespace App\Console\Commands;

use App\Models\Campsite;
use App\Models\CampsiteImage;
use App\Models\User;
use Database\Seeders\AmenitySeeder;
use Database\Seeders\CampsiteSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class EnsureDemoData extends Command
{
    protected $signature = 'app:ensure-demo-data {--force : Insert demo data even if campsites exist}';

    protected $description = 'Seed minimal demo data when the database is empty';

    public function handle(): int
    {
        try {
            // 例: DBファイルはあっても、migrate前だとここで失敗することがある
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $this->warn('DB connection is not ready. Run migrations first.');
            return self::FAILURE;
        }

        $hasCampsites = false;
        try {
            $hasCampsites = Campsite::query()->exists();
        } catch (Throwable $e) {
            $this->warn('Campsite table is not ready. Run migrations first.');
            return self::FAILURE;
        }

        if ($hasCampsites && ! $this->option('force')) {
            $this->info('Demo data already exists. Skip.');
            return self::SUCCESS;
        }

        // 予約や大量データは本番起動時に流し込まない（重複や負荷の原因になるため）
        Artisan::call('db:seed', ['--class' => AmenitySeeder::class, '--force' => true]);
        Artisan::call('db:seed', ['--class' => CampsiteSeeder::class, '--force' => true]);

        // 最低限の画像（タイプ別）を付与して一覧の見栄えを担保
        $typeToPublicPath = [
            'tent'     => 'images/find-your-style/tent.jpg',
            'auto'     => 'images/find-your-style/auto.jpg',
            'bungalow' => 'images/find-your-style/bungalow.jpg',
            'glamping' => 'images/find-your-style/glamping.jpg',
        ];

        $disk = Storage::disk('public');
        foreach (Campsite::query()->with('images')->get() as $campsite) {
            if ($campsite->images->isNotEmpty()) {
                continue;
            }

            $publicRel = $typeToPublicPath[$campsite->type] ?? null;
            if (! $publicRel) {
                continue;
            }

            $source = base_path('public/' . $publicRel);
            if (! is_file($source)) {
                continue;
            }

            $targetDir  = 'campsites';
            $targetName = Str::slug($campsite->name) ?: ('campsite-' . $campsite->id);
            $targetPath = $targetDir . '/' . $targetName . '-' . Str::random(6) . '.jpg';

            $disk->put($targetPath, file_get_contents($source));

            CampsiteImage::query()->create([
                'campsite_id' => $campsite->id,
                'image_path'  => $targetPath,
                'sort_order'  => 0,
            ]);
        }

        // README に載せている固定テストユーザー（無ければ作る）
        User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name'              => 'Test User',
                'phone'             => '000-0000-0000',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
            ]
        );

        $count = Campsite::query()->count();
        $this->info("Ensured demo data. campsites={$count}");

        return self::SUCCESS;
    }
}

