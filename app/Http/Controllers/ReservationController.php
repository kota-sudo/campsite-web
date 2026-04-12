<?php

namespace App\Http\Controllers;

use App\Mail\ReservationCancelled;
use App\Mail\ReservationConfirmed;
use App\Models\Campsite;
use App\Models\CampsitePlan;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(): View
    {
        $reservations = auth()->user()
            ->reservations()
            ->with('campsite')
            ->orderByDesc('check_in_date')
            ->paginate(10);

        return view('reservations.index', compact('reservations'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $request->validate([
            'campsite_id' => ['required', 'exists:campsites,id'],
            'plan_id'     => ['nullable', 'exists:campsite_plans,id'],
            'check_in'    => ['required', 'date', 'after_or_equal:today'],
            'check_out'   => ['required', 'date', 'after:check_in'],
            'guests'      => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $campsite = Campsite::findOrFail($request->campsite_id);
        $plan     = $request->plan_id ? CampsitePlan::findOrFail($request->plan_id) : null;
        $checkIn  = $request->check_in;
        $checkOut = $request->check_out;
        $guests   = (int) $request->guests;

        $maxCapacity = $plan ? $plan->capacity : $campsite->capacity;
        if ($guests > $maxCapacity) {
            return back()->withErrors(['guests' => '人数がプランの最大収容人数を超えています。']);
        }

        // ブラックアウト確認
        if ($campsite->hasBlockoutConflict($checkIn, $checkOut)) {
            return back()->withErrors(['check_in' => '選択した日程はメンテナンス等により予約をお受けできません。']);
        }

        // 空き確認: プランがある場合はプランレベル、ない場合はサイトレベル
        if ($plan) {
            if (! $plan->isAvailableFor($checkIn, $checkOut)) {
                return back()->withErrors(['check_in' => '選択した日程はすでに満席です。']);
            }
        } else {
            if ($campsite->reservations()->overlapping($checkIn, $checkOut)->exists()) {
                return back()->withErrors(['check_in' => '選択した日程はすでに予約済みです。']);
            }
        }

        $nights     = (int) (new \DateTime($checkIn))->diff(new \DateTime($checkOut))->days;
        $campsite->load('prices');
        $priceBase  = $plan ? $plan->price_per_night : null;
        $totalPrice = $plan
            ? $plan->price_per_night * $nights
            : $campsite->calculateTotalPrice($checkIn, $checkOut);

        return view('reservations.create', compact(
            'campsite', 'plan', 'checkIn', 'checkOut', 'guests', 'nights', 'totalPrice', 'priceBase'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campsite_id'     => ['required', 'exists:campsites,id'],
            'plan_id'         => ['nullable', 'exists:campsite_plans,id'],
            'check_in_date'   => ['required', 'date', 'after_or_equal:today'],
            'check_out_date'  => ['required', 'date', 'after:check_in_date'],
            'num_guests'      => ['required', 'integer', 'min:1'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['check_in_date'] >= $validated['check_out_date']) {
            return back()->withErrors([
                'check_out_date' => 'チェックアウト日はチェックイン日より後にしてください。'
            ])->withInput();
        }

        $campsite = Campsite::findOrFail($validated['campsite_id']);
        $plan     = ! empty($validated['plan_id'])
            ? CampsitePlan::findOrFail($validated['plan_id'])
            : null;

        $maxCapacity = $plan ? $plan->capacity : $campsite->capacity;
        if ($validated['num_guests'] > $maxCapacity) {
            return back()->withErrors([
                'num_guests' => '人数がプランの最大収容人数を超えています。'
            ])->withInput();
        }

        // ブラックアウト最終ガード
        if ($campsite->hasBlockoutConflict($validated['check_in_date'], $validated['check_out_date'])) {
            return back()->withErrors([
                'check_in_date' => '選択した日程はメンテナンス等により予約をお受けできません。'
            ])->withInput();
        }

        // 空き最終ガード
        if ($plan) {
            if (! $plan->isAvailableFor($validated['check_in_date'], $validated['check_out_date'])) {
                return back()->withErrors([
                    'check_in_date' => '選択した日程のプランはすでに満席です。'
                ])->withInput();
            }
        } else {
            if ($campsite->reservations()->overlapping($validated['check_in_date'], $validated['check_out_date'])->exists()) {
                return back()->withErrors([
                    'check_in_date' => '選択した日程はすでに予約済みです。別の日程をお選びください。'
                ])->withInput();
            }
        }

        $nights = (int) (new \DateTime($validated['check_in_date']))->diff(new \DateTime($validated['check_out_date']))->days;
        if ($plan) {
            $totalPrice = $plan->price_per_night * $nights;
        } else {
            $campsite->load('prices');
            $totalPrice = $campsite->calculateTotalPrice($validated['check_in_date'], $validated['check_out_date']);
        }

        $reservation = auth()->user()->reservations()->create([
            'campsite_id'     => $campsite->id,
            'campsite_plan_id'=> $plan?->id,
            'check_in_date'   => $validated['check_in_date'],
            'check_out_date'  => $validated['check_out_date'],
            'num_guests'      => $validated['num_guests'],
            'total_price'     => $totalPrice,
            'status'          => 'pending',
            'notes'           => $validated['notes'] ?? null,
        ]);

        $reservation->load(['campsite', 'user']);
        Mail::to($reservation->user)->send(new ReservationConfirmed($reservation));

        return redirect()->route('reservations.complete', $reservation);
    }

    public function complete(Reservation $reservation): View
    {
        Gate::authorize('view', $reservation);
        $reservation->load(['campsite.images']);
        return view('reservations.complete', compact('reservation'));
    }

    public function show(Reservation $reservation): View
    {
        Gate::authorize('view', $reservation);

        $reservation->load(['campsite.images', 'review']);

        return view('reservations.show', compact('reservation'));
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        Gate::authorize('delete', $reservation);

        if (! $reservation->isCancellable()) {
            return back()->withErrors(['status' => 'この予約はキャンセルできません。']);
        }

        $reservation->update(['status' => 'cancelled']);

        $reservation->load(['campsite', 'user']);
        Mail::to($reservation->user)->send(new ReservationCancelled($reservation));

        return redirect()->route('reservations.index')
            ->with('success', '予約をキャンセルしました。キャンセル確認メールをお送りしました。');
    }
}
