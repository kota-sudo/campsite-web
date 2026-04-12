<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Campsite;
use App\Models\CampsitePrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampsiteController extends Controller
{
    public function index(): View
    {
        $campsites = Campsite::with('amenities')->orderBy('id')->paginate(20);

        return view('admin.campsites.index', compact('campsites'));
    }

    public function create(): View
    {
        $amenities = Amenity::orderBy('name')->get();

        return view('admin.campsites.create', compact('amenities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'type'            => ['required', 'in:tent,auto,bungalow,glamping'],
            'capacity'        => ['required', 'integer', 'min:1', 'max:50'],
            'price_per_night' => ['required', 'integer', 'min:100'],
            'amenity_ids'     => ['nullable', 'array'],
            'amenity_ids.*'   => ['exists:amenities,id'],
            'address'         => ['nullable', 'string', 'max:200'],
            'latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'       => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $campsite = Campsite::create([
            'name'            => $validated['name'],
            'description'     => $validated['description'] ?? null,
            'type'            => $validated['type'],
            'capacity'        => $validated['capacity'],
            'price_per_night' => $validated['price_per_night'],
            'is_active'       => $request->boolean('is_active'),
            'address'         => $validated['address'] ?? null,
            'latitude'        => $validated['latitude'] ?? null,
            'longitude'       => $validated['longitude'] ?? null,
        ]);

        $campsite->amenities()->sync($validated['amenity_ids'] ?? []);

        return redirect()->route('admin.campsites.index')
            ->with('success', 'キャンプサイトを追加しました。');
    }

    public function edit(Campsite $campsite): View
    {
        $campsite->load(['amenities', 'prices' => fn ($q) => $q->orderBy('start_date')]);
        $amenities = Amenity::orderBy('name')->get();

        return view('admin.campsites.edit', compact('campsite', 'amenities'));
    }

    public function storePrice(Request $request, Campsite $campsite): RedirectResponse
    {
        $request->validate([
            'label'          => ['nullable', 'string', 'max:50'],
            'start_date'     => ['required', 'date'],
            'end_date'       => ['required', 'date', 'after_or_equal:start_date'],
            'price_per_night'=> ['required', 'integer', 'min:100'],
        ]);

        $campsite->prices()->create($request->only('label', 'start_date', 'end_date', 'price_per_night'));

        return back()->with('success', '特別価格を追加しました。');
    }

    public function destroyPrice(Campsite $campsite, CampsitePrice $price): RedirectResponse
    {
        $price->delete();
        return back()->with('success', '特別価格を削除しました。');
    }

    public function update(Request $request, Campsite $campsite): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'type'            => ['required', 'in:tent,auto,bungalow,glamping'],
            'capacity'        => ['required', 'integer', 'min:1', 'max:50'],
            'price_per_night' => ['required', 'integer', 'min:100'],
            'amenity_ids'     => ['nullable', 'array'],
            'amenity_ids.*'   => ['exists:amenities,id'],
            'address'         => ['nullable', 'string', 'max:200'],
            'latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'       => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $campsite->update([
            'name'            => $validated['name'],
            'description'     => $validated['description'] ?? null,
            'type'            => $validated['type'],
            'capacity'        => $validated['capacity'],
            'price_per_night' => $validated['price_per_night'],
            'is_active'       => $request->boolean('is_active'),
            'address'         => $validated['address'] ?? null,
            'latitude'        => $validated['latitude'] ?? null,
            'longitude'       => $validated['longitude'] ?? null,
        ]);

        $campsite->amenities()->sync($validated['amenity_ids'] ?? []);

        return redirect()->route('admin.campsites.index')
            ->with('success', 'キャンプサイトを更新しました。');
    }

    public function approve(Campsite $campsite): RedirectResponse
    {
        $campsite->update(['is_active' => true]);

        return redirect()->route('admin.campsites.index')
            ->with('success', "「{$campsite->name}」を承認・公開しました。");
    }

    public function destroy(Campsite $campsite): RedirectResponse
    {
        // 物理削除ではなく非公開化
        $campsite->update(['is_active' => false]);

        return redirect()->route('admin.campsites.index')
            ->with('success', 'キャンプサイトを非公開にしました。');
    }
}
