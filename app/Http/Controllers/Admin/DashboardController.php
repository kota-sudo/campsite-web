<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campsite;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // --- KPI ---
        $totalReservations   = Reservation::count();
        $confirmedCount      = Reservation::where('status', 'confirmed')->count();
        $cancelledCount      = Reservation::where('status', 'cancelled')->count();
        $totalRevenue        = Reservation::whereIn('status', ['confirmed', 'pending'])
                                    ->sum('total_price');
        $totalCampsites      = Campsite::where('is_active', true)->count();
        $totalUsers          = User::where('is_admin', false)->count();

        // --- 月別予約数・売上 (過去12ヶ月) ---
        $monthly = Reservation::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->whereIn('status', ['confirmed', 'pending'])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // 全12ヶ月分を埋める
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $monthlyData[$key] = ['month' => $key, 'count' => 0, 'revenue' => 0];
        }
        foreach ($monthly as $row) {
            if (isset($monthlyData[$row->month])) {
                $monthlyData[$row->month]['count']   = (int) $row->count;
                $monthlyData[$row->month]['revenue'] = (float) $row->revenue;
            }
        }
        $monthlyData = array_values($monthlyData);

        // --- サイト別稼働率 (確定+保留 / 全日数で近似) ---
        $siteStats = Campsite::where('is_active', true)
            ->withCount([
                'reservations as active_reservations_count' => fn ($q) =>
                    $q->whereIn('status', ['confirmed', 'pending'])
            ])
            ->with([
                'reservations' => fn ($q) =>
                    $q->whereIn('status', ['confirmed', 'pending'])
                      ->select('campsite_id', 'check_in_date', 'check_out_date')
            ])
            ->orderByDesc('active_reservations_count')
            ->take(10)
            ->get()
            ->map(function ($site) {
                $totalNights = $site->reservations->sum(fn ($r) =>
                    $r->check_in_date->diffInDays($r->check_out_date)
                );
                // 過去180日間に対する稼働率
                $site->occupancy_rate = min(100, round($totalNights / 180 * 100, 1));
                $site->total_revenue  = $site->reservations->sum('total_price');
                return $site;
            });

        // --- 直近の予約 ---
        $recentReservations = Reservation::with(['user', 'campsite'])
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        // --- ステータス別件数 ---
        $statusCounts = Reservation::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('admin.dashboard', compact(
            'totalReservations', 'confirmedCount', 'cancelledCount',
            'totalRevenue', 'totalCampsites', 'totalUsers',
            'monthlyData', 'siteStats', 'recentReservations', 'statusCounts'
        ));
    }
}
