<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Host;
use App\Http\Controllers\Host\CampsitePlanController as HostCampsitePlanController;
use App\Http\Controllers\Host\CampsiteBlockoutController as HostCampsiteBlockoutController;
use App\Http\Controllers\Host\ReservationController as HostReservationController;
use App\Http\Controllers\NearbySpotController;
use App\Http\Controllers\CampsiteCompareController;
use App\Http\Controllers\CampsiteController;
use App\Http\Controllers\CampsiteQuestionController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\NaturalSearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// お問い合わせ
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
Route::get('/contact/complete', [ContactController::class, 'complete'])->name('contact.complete');

// チャットAPI
Route::post('/chat/message', [ChatController::class, 'chat'])->middleware('throttle:chat')->name('chat.message');
Route::post('/chat/reset', [ChatController::class, 'reset'])->name('chat.reset');

// キャンプサイト (公開)
Route::get('/campsites', [CampsiteController::class, 'index'])->name('campsites.index');
Route::get('/campsites/compare', [CampsiteCompareController::class, 'show'])->name('campsites.compare');
Route::get('/campsites/{campsite}', [CampsiteController::class, 'show'])->name('campsites.show');
Route::get('/campsites/{campsite}/booked-dates', [CampsiteController::class, 'bookedDates'])->name('campsites.booked-dates');

// 天気API (プロキシ)
Route::get('/api/weather', [WeatherController::class, 'show'])->name('weather.show');

// 周辺観光・登山スポットAPI (Overpass プロキシ)
Route::get('/api/nearby-spots', [NearbySpotController::class, 'show'])->name('nearby-spots.show');

// 自然言語検索API
Route::post('/api/search-natural', [NaturalSearchController::class, 'parse'])->name('search.natural');

// Q&A (質問は要ログイン、回答もホスト/管理者のみ)
Route::post('/campsites/{campsite}/questions', [CampsiteQuestionController::class, 'store'])
    ->middleware('auth')
    ->name('campsite.questions.store');
Route::patch('/campsites/{campsite}/questions/{question}/answer', [CampsiteQuestionController::class, 'answer'])
    ->middleware('auth')
    ->name('campsite.questions.answer');

// 予約 (要ログイン)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation}/complete', [ReservationController::class, 'complete'])->name('reservations.complete');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
    // レビュー
    Route::post('/reservations/{reservation}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

// ダッシュボード → マイ予約にリダイレクト
Route::get('/dashboard', function () {
    return redirect()->route('reservations.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// お気に入り
Route::get('/favorites/share/{token}', [FavoriteController::class, 'showShared'])->name('favorites.shared');
Route::middleware(['auth'])->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{campsite}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::post('/favorites/share-token/generate', [FavoriteController::class, 'generateShareToken'])->name('favorites.share.generate');
    Route::delete('/favorites/share-token', [FavoriteController::class, 'revokeShareToken'])->name('favorites.share.revoke');
});

// ホストポータル
Route::middleware(['auth', 'verified', 'host'])->prefix('host')->name('host.')->group(function () {
    Route::get('/', [Host\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('campsites', Host\CampsiteController::class)->except(['show', 'destroy']);
    Route::get('campsites/{campsite}/reservations', [Host\CampsiteController::class, 'reservations'])->name('campsites.reservations');
    Route::patch('campsites/{campsite}/toggle-active', [Host\CampsiteController::class, 'toggleActive'])->name('campsites.toggle-active');
    Route::post('campsites/{campsite}/prices', [Host\CampsiteController::class, 'storePrice'])->name('campsites.prices.store');
    Route::delete('campsites/{campsite}/prices/{price}', [Host\CampsiteController::class, 'destroyPrice'])->name('campsites.prices.destroy');
    // プラン管理
    Route::post('campsites/{campsite}/plans', [HostCampsitePlanController::class, 'store'])->name('campsites.plans.store');
    Route::patch('campsites/{campsite}/plans/{plan}', [HostCampsitePlanController::class, 'update'])->name('campsites.plans.update');
    Route::delete('campsites/{campsite}/plans/{plan}', [HostCampsitePlanController::class, 'destroy'])->name('campsites.plans.destroy');
    // ブラックアウト管理
    Route::post('campsites/{campsite}/blockouts', [HostCampsiteBlockoutController::class, 'store'])->name('campsites.blockouts.store');
    Route::delete('campsites/{campsite}/blockouts/{blockout}', [HostCampsiteBlockoutController::class, 'destroy'])->name('campsites.blockouts.destroy');
    Route::patch('reservations/{reservation}/approve', [HostReservationController::class, 'approve'])->name('reservations.approve');
});

// 管理画面
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('campsites', Admin\CampsiteController::class)->except(['show']);
    Route::patch('campsites/{campsite}/approve', [Admin\CampsiteController::class, 'approve'])->name('campsites.approve');
    Route::post('campsites/{campsite}/prices', [Admin\CampsiteController::class, 'storePrice'])->name('campsites.prices.store');
    Route::delete('campsites/{campsite}/prices/{price}', [Admin\CampsiteController::class, 'destroyPrice'])->name('campsites.prices.destroy');
    Route::get('reservations', [Admin\ReservationController::class, 'index'])->name('reservations.index');
    Route::patch('reservations/{reservation}', [Admin\ReservationController::class, 'update'])->name('reservations.update');
    Route::get('contacts', [Admin\ContactController::class, 'index'])->name('contacts.index');
    Route::patch('contacts/{contact}', [Admin\ContactController::class, 'update'])->name('contacts.update');
});

require __DIR__.'/auth.php';
