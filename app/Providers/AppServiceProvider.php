<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Vite manifest 経由の CSS/JS: ルート相対にする（リバースプロキシで http URL になり mixed content になるのを防ぐ）
        // ※ npm run dev（public/hot あり）のときは @vite が別経路のためこのコールバックは使われない
        Vite::createAssetPathsUsing(function (string $path, ?bool $secure = null): string {
            return '/'.ltrim($path, '/');
        });

        // チャット: 1分間に20回まで（ユーザー or IP）
        RateLimiter::for('chat', function (Request $request) {
            return Limit::perMinute(20)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'reply' => 'メッセージの送信回数が多すぎます。少し待ってから再送信してください。',
                    ], 429);
                });
        });

        // お問い合わせ: 1時間に5回まで（IP）
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perHour(5)
                ->by($request->ip())
                ->response(function () {
                    return back()->withErrors([
                        'submit' => '送信回数が上限に達しました。しばらく時間をおいてから再度お試しください。',
                    ]);
                });
        });
    }
}
