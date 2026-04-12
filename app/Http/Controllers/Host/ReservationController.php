<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;

class ReservationController extends Controller
{
    public function approve(Reservation $reservation): RedirectResponse
    {
        // 自分のサイトへの予約のみ操作可能
        $campsite = $reservation->campsite;
        if ($campsite->user_id !== auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }

        if ($reservation->status !== 'pending') {
            return back()->withErrors(['status' => 'この予約は変更できません。']);
        }

        $reservation->update(['status' => 'confirmed']);

        return back()->with('success', '予約を承認しました。');
    }
}
