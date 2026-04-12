<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $campsites = $user->ownedCampsites()
            ->withCount([
                'reservations as total_reservations',
                'reservations as active_reservations' => fn ($q) =>
                    $q->whereIn('status', ['confirmed', 'pending']),
            ])
            ->orderByDesc('created_at')
            ->get();

        $campsiteIds = $campsites->pluck('id');

        // KPI
        $totalRevenue = Reservation::whereIn('campsite_id', $campsiteIds)
            ->whereIn('status', ['confirmed', 'pending'])
            ->sum('total_price');

        $pendingCount = Reservation::whereIn('campsite_id', $campsiteIds)
            ->where('status', 'pending')
            ->count();

        // 月別売上（過去6ヶ月）
        $monthly = Reservation::select(
                DB::raw("strftime('%Y-%m', created_at) as month"),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->whereIn('campsite_id', $campsiteIds)
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $monthlyData[] = [
                'month'   => $key,
                'revenue' => (float) ($monthly[$key]->revenue ?? 0),
                'count'   => (int)   ($monthly[$key]->count   ?? 0),
            ];
        }

        // 直近の予約
        $recentReservations = Reservation::with(['user', 'campsite'])
            ->whereIn('campsite_id', $campsiteIds)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('host.dashboard', compact(
            'campsites', 'totalRevenue', 'pendingCount', 'monthlyData', 'recentReservations'
        ));
    }
}
