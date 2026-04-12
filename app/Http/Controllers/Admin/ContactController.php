<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $query = Contact::with('user')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $contacts  = $query->paginate(20)->withQueryString();
        $newCount  = Contact::where('status', 'new')->count();

        return view('admin.contacts.index', compact('contacts', 'newCount'));
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $request->validate([
            'status'     => ['required', 'in:new,in_progress,resolved'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $contact->update($request->only('status', 'admin_note'));

        return back()->with('success', 'ステータスを更新しました。');
    }
}
