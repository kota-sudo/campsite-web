<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Reservation::with(['user', 'campsite'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('campsite_id')) {
            $query->where('campsite_id', $request->campsite_id);
        }

        $reservations = $query->paginate(20)->withQueryString();

        return view('admin.reservations.index', compact('reservations'));
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled'],
        ]);

        $reservation->update(['status' => $request->status]);

        return back()->with('success', '予約ステータスを更新しました。');
    }
}
