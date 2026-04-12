<?php

namespace App\Http\Controllers;

use App\Models\Campsite;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampsiteCompareController extends Controller
{
    public function show(Request $request): View
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:2', 'max:3'],
            'ids.*' => ['integer', 'exists:campsites,id'],
        ]);

        $ids       = array_unique($request->input('ids'));
        $campsites = Campsite::with(['images', 'amenities', 'reviews'])
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($c) => array_search($c->id, $ids))
            ->values();

        // 全アメニティを収集して比較マトリクスを作る
        $allAmenities = $campsites->flatMap->amenities->unique('id')->sortBy('name');

        return view('campsites.compare', compact('campsites', 'allAmenities'));
    }
}
