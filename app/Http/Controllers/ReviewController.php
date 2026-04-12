<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function store(Request $request, Reservation $reservation): RedirectResponse
    {
        Gate::authorize('view', $reservation);

        if (! $reservation->isReviewable()) {
            return back()->withErrors(['review' => 'この予約にはレビューを投稿できません。']);
        }

        $validated = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        Review::create([
            'user_id'        => auth()->id(),
            'campsite_id'    => $reservation->campsite_id,
            'reservation_id' => $reservation->id,
            'rating'         => $validated['rating'],
            'comment'        => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'レビューを投稿しました。');
    }

    public function destroy(Review $review): RedirectResponse
    {
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $review->delete();

        return back()->with('success', 'レビューを削除しました。');
    }
}
