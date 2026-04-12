<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Campsite;
use App\Models\CampsiteBlockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CampsiteBlockoutController extends Controller
{
    public function store(Request $request, Campsite $campsite): RedirectResponse
    {
        $this->authorizeOwnership($campsite);

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'reason'     => ['nullable', 'string', 'max:100'],
        ]);

        $campsite->blockouts()->create($request->only('start_date', 'end_date', 'reason'));

        return back()->with('success', 'ブラックアウト日を追加しました。');
    }

    public function destroy(Campsite $campsite, CampsiteBlockout $blockout): RedirectResponse
    {
        $this->authorizeOwnership($campsite);
        abort_unless($blockout->campsite_id === $campsite->id, 404);

        $blockout->delete();

        return back()->with('success', 'ブラックアウト日を削除しました。');
    }

    private function authorizeOwnership(Campsite $campsite): void
    {
        if ($campsite->user_id !== auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }
    }
}
