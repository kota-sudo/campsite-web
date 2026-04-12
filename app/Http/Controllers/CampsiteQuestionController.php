<?php

namespace App\Http\Controllers;

use App\Models\Campsite;
use App\Models\CampsiteQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CampsiteQuestionController extends Controller
{
    public function store(Request $request, Campsite $campsite): RedirectResponse
    {
        $request->validate([
            'body' => ['required', 'string', 'max:500'],
        ]);

        $campsite->questions()->create([
            'user_id' => auth()->id(),
            'body'    => $request->body,
        ]);

        return back()->with('success', '質問を送信しました。ホストからの回答をお待ちください。');
    }

    public function answer(Request $request, Campsite $campsite, CampsiteQuestion $question): RedirectResponse
    {
        // ホストまたは管理者のみ
        abort_unless(
            auth()->id() === $campsite->user_id || auth()->user()->is_admin,
            403
        );

        $request->validate([
            'answer_body' => ['required', 'string', 'max:1000'],
        ]);

        $question->update([
            'answer_body' => $request->answer_body,
            'answered_by' => auth()->id(),
            'answered_at' => now(),
        ]);

        return back()->with('success', '回答を投稿しました。');
    }
}
