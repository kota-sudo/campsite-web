<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Campsite;
use App\Models\CampsitePlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CampsitePlanController extends Controller
{
    public function store(Request $request, Campsite $campsite): RedirectResponse
    {
        $this->authorizeOwnership($campsite);

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:500'],
            'price_per_night' => ['required', 'integer', 'min:100'],
            'capacity'        => ['required', 'integer', 'min:1', 'max:50'],
            'stock'           => ['required', 'integer', 'min:1', 'max:99'],
            'sort_order'      => ['nullable', 'integer', 'min:0'],
            'image'           => ['nullable', 'image', 'max:5120'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('plans', 'public');
        }

        $campsite->plans()->create([
            'name'            => $validated['name'],
            'description'     => $validated['description'] ?? null,
            'image_path'      => $imagePath,
            'price_per_night' => $validated['price_per_night'],
            'capacity'        => $validated['capacity'],
            'stock'           => $validated['stock'],
            'sort_order'      => $validated['sort_order'] ?? $campsite->plans()->count(),
            'is_active'       => true,
        ]);

        return back()->with('success', 'プランを追加しました。');
    }

    public function update(Request $request, Campsite $campsite, CampsitePlan $plan): RedirectResponse
    {
        $this->authorizeOwnership($campsite);
        abort_unless($plan->campsite_id === $campsite->id, 404);

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:500'],
            'price_per_night' => ['required', 'integer', 'min:100'],
            'capacity'        => ['required', 'integer', 'min:1', 'max:50'],
            'stock'           => ['required', 'integer', 'min:1', 'max:99'],
            'sort_order'      => ['nullable', 'integer', 'min:0'],
            'is_active'       => ['boolean'],
            'image'           => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            if ($plan->image_path) {
                Storage::disk('public')->delete($plan->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('plans', 'public');
        }

        $plan->update([
            'name'            => $validated['name'],
            'description'     => $validated['description'] ?? null,
            'price_per_night' => $validated['price_per_night'],
            'capacity'        => $validated['capacity'],
            'stock'           => $validated['stock'],
            'sort_order'      => $validated['sort_order'] ?? $plan->sort_order,
            'is_active'       => $request->boolean('is_active', true),
            'image_path'      => $validated['image_path'] ?? $plan->image_path,
        ]);

        return back()->with('success', 'プランを更新しました。');
    }

    public function destroy(Campsite $campsite, CampsitePlan $plan): RedirectResponse
    {
        $this->authorizeOwnership($campsite);
        abort_unless($plan->campsite_id === $campsite->id, 404);

        if ($plan->image_path) {
            Storage::disk('public')->delete($plan->image_path);
        }

        $plan->delete();

        return back()->with('success', 'プランを削除しました。');
    }

    private function authorizeOwnership(Campsite $campsite): void
    {
        if ($campsite->user_id !== auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }
    }
}
