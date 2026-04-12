<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Campsite;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampsiteController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'check_in'    => ['nullable', 'date', 'after_or_equal:today'],
            'check_out'   => ['nullable', 'date', 'after:check_in'],
            'guests'      => ['nullable', 'integer', 'min:1', 'max:20'],
            'type'        => ['nullable', 'in:tent,auto,bungalow,glamping'],
            'sort'        => ['nullable', 'in:price_asc,price_desc,capacity_desc,rating_desc'],
            'price_min'   => ['nullable', 'integer', 'min:0'],
            'price_max'   => ['nullable', 'integer', 'min:0'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id'],
            'min_rating'  => ['nullable', 'numeric', 'min:1', 'max:5'],
        ]);

        $query = Campsite::with(['images', 'amenities', 'reviews'])
            ->where('is_active', true);

        if (!empty($validated['guests'])) {
            $query->where('capacity', '>=', (int) $validated['guests']);
        }

        if (!empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (!empty($validated['price_min'])) {
            $query->where('price_per_night', '>=', (int) $validated['price_min']);
        }

        if (!empty($validated['price_max'])) {
            $query->where('price_per_night', '<=', (int) $validated['price_max']);
        }

        if (!empty($validated['amenity_ids'])) {
            foreach ($validated['amenity_ids'] as $amenityId) {
                $query->whereHas('amenities', fn ($q) => $q->where('amenities.id', $amenityId));
            }
        }

        if (!empty($validated['min_rating'])) {
            // (float) でサニタイズ済みなのでSQLに直接埋め込んでも安全
            $minRating = (float) $validated['min_rating'];
            $query->whereRaw(
                'exists (select 1 from reviews where reviews.campsite_id = campsites.id group by campsite_id having AVG(rating) >= ' . $minRating . ')'
            );
        }

        if (!empty($validated['check_in']) && !empty($validated['check_out'])) {
            $checkIn  = $validated['check_in'];
            $checkOut = $validated['check_out'];

            $query->whereDoesntHave('reservations', function ($q) use ($checkIn, $checkOut) {
                $q->overlapping($checkIn, $checkOut);
            });
        }

        if (($validated['sort'] ?? '') === 'price_asc') {
            $query->orderBy('price_per_night', 'asc');
        } elseif (($validated['sort'] ?? '') === 'price_desc') {
            $query->orderBy('price_per_night', 'desc');
        } elseif (($validated['sort'] ?? '') === 'capacity_desc') {
            $query->orderBy('capacity', 'desc');
        } elseif (($validated['sort'] ?? '') === 'rating_desc') {
            $query->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating');
        } else {
            $query->orderBy('price_per_night', 'asc');
        }

        $campsites = $query->paginate(9)->withQueryString();

        $favoriteIds = auth()->check()
            ? auth()->user()->favoriteCampsites()->pluck('campsite_id')->toArray()
            : [];

        $amenities = Amenity::orderBy('name')->get();

        // 最近見たサイト（セッション）
        $recentIds     = session('recently_viewed', []);
        $recentSites   = $recentIds
            ? Campsite::with('images')->whereIn('id', $recentIds)->where('is_active', true)->get()
                ->sortBy(fn ($c) => array_search($c->id, $recentIds))->values()
            : collect();

        return view('campsites.index', compact('campsites', 'favoriteIds', 'amenities', 'recentSites'));
    }

    public function show(Request $request, Campsite $campsite): View
    {
    $validated = $request->validate([
        'check_in'  => ['nullable', 'date', 'after_or_equal:today'],
        'check_out' => ['nullable', 'date', 'after:check_in'],
    ]);

    $campsite->load(['images', 'amenities', 'reviews.user', 'questions.user', 'activePlans']);

    // 最近見たサイトをセッションに記録（先頭に追加、最大5件）
    $recent = session('recently_viewed', []);
    $recent = array_values(array_filter($recent, fn ($id) => $id !== $campsite->id));
    array_unshift($recent, $campsite->id);
    session(['recently_viewed' => array_slice($recent, 0, 5)]);

    $isAvailable = null;
    if (!empty($validated['check_in']) && !empty($validated['check_out'])) {
        $isAvailable = ! $campsite->reservations()
            ->overlapping($validated['check_in'], $validated['check_out'])
            ->exists();
    }

    $isFavorited = auth()->check()
        && auth()->user()->favoriteCampsites()->where('campsite_id', $campsite->id)->exists();

    $userReviewableReservation = null;
    if (auth()->check()) {
        $userReviewableReservation = $campsite->reservations()
            ->where('user_id', auth()->id())
            ->where('status', 'confirmed')
            ->where('check_out_date', '<', now())
            ->whereDoesntHave('review')
            ->latest('check_out_date')
            ->first();
    }

    return view('campsites.show', compact('campsite', 'isAvailable', 'isFavorited', 'userReviewableReservation'));
}

    /**
     * 指定月の予約済み日付を返す (カレンダー用 JSON API)
     */
    public function bookedDates(Request $request, Campsite $campsite): JsonResponse
    {
        $year  = (int) $request->query('year',  now()->year);
        $month = (int) $request->query('month', now()->month);

        $from = sprintf('%04d-%02d-01', $year, $month);
        $to   = date('Y-m-t', strtotime($from)); // 月末日

        $reservations = $campsite->reservations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('check_in_date', '<=', $to)
            ->where('check_out_date', '>', $from)
            ->get(['check_in_date', 'check_out_date']);

        $booked = [];
        foreach ($reservations as $res) {
            $cur = max(strtotime($res->check_in_date->toDateString()), strtotime($from));
            // check_out_date は宿泊しない日なので含めない
            $end = strtotime($res->check_out_date->toDateString());
            while ($cur < $end) {
                $booked[] = date('Y-m-d', $cur);
                $cur = strtotime('+1 day', $cur);
            }
        }

        // ブラックアウト日を blocked に含める
        $blockoutRanges = $campsite->blockouts()
            ->where('start_date', '<=', $to)
            ->where('end_date',   '>=', $from)
            ->get(['start_date', 'end_date', 'reason']);

        $blocked = [];
        $blockoutReasons = [];
        foreach ($blockoutRanges as $b) {
            $cur = max(strtotime($b->start_date->toDateString()), strtotime($from));
            $end = strtotime(date('Y-m-d', strtotime($b->end_date->toDateString() . ' +1 day')));
            while ($cur < $end && $cur <= strtotime($to)) {
                $dateStr = date('Y-m-d', $cur);
                $blocked[] = $dateStr;
                if ($b->reason) {
                    $blockoutReasons[$dateStr] = $b->reason;
                }
                $cur = strtotime('+1 day', $cur);
            }
        }

        $campsite->load('prices');
        $priceMap = $campsite->getPriceMapForMonth($year, $month);

        return response()->json([
            'booked'          => array_values(array_unique($booked)),
            'blocked'         => array_values(array_unique($blocked)),
            'blockoutReasons' => $blockoutReasons,
            'priceMap'        => $priceMap,
            'basePrice'       => $campsite->price_per_night,
        ]);
    }
}
