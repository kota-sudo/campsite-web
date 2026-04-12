<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Campsite;
use App\Models\CampsitePrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CampsiteController extends Controller
{
    public function index(): View
    {
        $campsites = auth()->user()->ownedCampsites()
            ->with(['images', 'reservations' => fn ($q) => $q->whereIn('status', ['confirmed', 'pending'])])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('host.campsites.index', compact('campsites'));
    }

    public function create(): View
    {
        $amenities = Amenity::orderBy('name')->get();
        return view('host.campsites.create', compact('amenities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:100'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'type'               => ['required', 'in:tent,auto,bungalow,glamping'],
            'capacity'           => ['required', 'integer', 'min:1', 'max:50'],
            'price_per_night'    => ['required', 'integer', 'min:100'],
            'weekend_multiplier' => ['nullable', 'numeric', 'min:1.0', 'max:5.0'],
            'address'            => ['nullable', 'string', 'max:200'],
            'latitude'           => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'          => ['nullable', 'numeric', 'between:-180,180'],
            'amenity_ids'        => ['nullable', 'array'],
            'amenity_ids.*'      => ['exists:amenities,id'],
            'images.*'           => ['nullable', 'image', 'max:5120'],
        ]);

        $campsite = auth()->user()->ownedCampsites()->create([
            'name'               => $validated['name'],
            'description'        => $validated['description'] ?? null,
            'type'               => $validated['type'],
            'capacity'           => $validated['capacity'],
            'price_per_night'    => $validated['price_per_night'],
            'weekend_multiplier' => $validated['weekend_multiplier'] ?? 1.0,
            'address'            => $validated['address'] ?? null,
            'latitude'           => $validated['latitude'] ?? null,
            'longitude'          => $validated['longitude'] ?? null,
            'is_active'          => false, // 管理者が承認するまで非公開
        ]);

        $campsite->amenities()->sync($validated['amenity_ids'] ?? []);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $file) {
                $path = $file->store('campsites', 'public');
                $campsite->images()->create(['image_path' => $path, 'sort_order' => $i]);
            }
        }

        return redirect()->route('host.campsites.index')
            ->with('success', 'サイトを登録しました。管理者が確認後に公開されます。');
    }

    public function edit(Campsite $campsite): View
    {
        $this->authorizeOwnership($campsite);
        $campsite->load([
            'amenities',
            'images',
            'prices'    => fn ($q) => $q->orderBy('start_date'),
            'plans'     => fn ($q) => $q->orderBy('sort_order'),
            'blockouts' => fn ($q) => $q->orderBy('start_date'),
        ]);
        $amenities = Amenity::orderBy('name')->get();

        return view('host.campsites.edit', compact('campsite', 'amenities'));
    }

    public function update(Request $request, Campsite $campsite): RedirectResponse
    {
        $this->authorizeOwnership($campsite);

        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:100'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'type'               => ['required', 'in:tent,auto,bungalow,glamping'],
            'capacity'           => ['required', 'integer', 'min:1', 'max:50'],
            'price_per_night'    => ['required', 'integer', 'min:100'],
            'weekend_multiplier' => ['nullable', 'numeric', 'min:1.0', 'max:5.0'],
            'address'            => ['nullable', 'string', 'max:200'],
            'latitude'           => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'          => ['nullable', 'numeric', 'between:-180,180'],
            'amenity_ids'        => ['nullable', 'array'],
            'amenity_ids.*'      => ['exists:amenities,id'],
            'images.*'           => ['nullable', 'image', 'max:5120'],
        ]);

        $campsite->update([
            'name'               => $validated['name'],
            'description'        => $validated['description'] ?? null,
            'type'               => $validated['type'],
            'capacity'           => $validated['capacity'],
            'price_per_night'    => $validated['price_per_night'],
            'weekend_multiplier' => $validated['weekend_multiplier'] ?? 1.0,
            'address'            => $validated['address'] ?? null,
            'latitude'           => $validated['latitude'] ?? null,
            'longitude'          => $validated['longitude'] ?? null,
        ]);

        $campsite->amenities()->sync($validated['amenity_ids'] ?? []);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $file) {
                $path = $file->store('campsites', 'public');
                $campsite->images()->create([
                    'image_path'  => $path,
                    'sort_order'  => $campsite->images()->count() + $i,
                ]);
            }
        }

        return redirect()->route('host.campsites.index')
            ->with('success', 'サイト情報を更新しました。');
    }

    public function reservations(Campsite $campsite): View
    {
        $this->authorizeOwnership($campsite);

        $reservations = $campsite->reservations()
            ->with('user')
            ->orderByDesc('check_in_date')
            ->paginate(15);

        return view('host.campsites.reservations', compact('campsite', 'reservations'));
    }

    public function toggleActive(Campsite $campsite): RedirectResponse
    {
        $this->authorizeOwnership($campsite);
        $campsite->update(['is_active' => ! $campsite->is_active]);

        $msg = $campsite->is_active ? 'サイトを公開しました。' : 'サイトを非公開にしました。';
        return back()->with('success', $msg);
    }

    public function storePrice(Request $request, Campsite $campsite): RedirectResponse
    {
        $this->authorizeOwnership($campsite);

        $request->validate([
            'label'           => ['nullable', 'string', 'max:50'],
            'start_date'      => ['required', 'date'],
            'end_date'        => ['required', 'date', 'after_or_equal:start_date'],
            'price_per_night' => ['required', 'integer', 'min:100'],
        ]);

        $campsite->prices()->create($request->only('label', 'start_date', 'end_date', 'price_per_night'));

        return back()->with('success', '特別料金を追加しました。');
    }

    public function destroyPrice(Campsite $campsite, CampsitePrice $price): RedirectResponse
    {
        $this->authorizeOwnership($campsite);
        abort_unless($price->campsite_id === $campsite->id, 404);

        $price->delete();

        return back()->with('success', '特別料金を削除しました。');
    }

    private function authorizeOwnership(Campsite $campsite): void
    {
        if ($campsite->user_id !== auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }
    }
}
