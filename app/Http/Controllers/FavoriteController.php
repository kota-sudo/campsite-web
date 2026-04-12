<?php

namespace App\Http\Controllers;

use App\Models\Campsite;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(): View
    {
        $campsites = auth()->user()
            ->favoriteCampsites()
            ->with(['images', 'amenities', 'reviews'])
            ->where('is_active', true)
            ->paginate(9);

        return view('favorites.index', compact('campsites'));
    }

    public function generateShareToken(): RedirectResponse
    {
        $user = auth()->user();
        $user->update(['favorites_share_token' => Str::random(48)]);
        return back()->with('share_url', route('favorites.shared', $user->favorites_share_token));
    }

    public function revokeShareToken(): RedirectResponse
    {
        auth()->user()->update(['favorites_share_token' => null]);
        return back()->with('success', '共有リンクを無効にしました。');
    }

    public function showShared(string $token): View
    {
        $user = User::where('favorites_share_token', $token)->firstOrFail();

        $campsites = $user->favoriteCampsites()
            ->with(['images', 'amenities', 'reviews'])
            ->where('is_active', true)
            ->paginate(12);

        return view('favorites.shared', compact('user', 'campsites'));
    }

    public function toggle(Request $request, Campsite $campsite): JsonResponse
    {
        $user = auth()->user();

        if ($user->favoriteCampsites()->where('campsite_id', $campsite->id)->exists()) {
            $user->favoriteCampsites()->detach($campsite->id);
            $favorited = false;
        } else {
            $user->favoriteCampsites()->attach($campsite->id);
            $favorited = true;
        }

        return response()->json(['favorited' => $favorited]);
    }
}
