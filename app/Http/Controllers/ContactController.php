<?php

namespace App\Http\Controllers;

use App\Mail\ContactReceived;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('contact.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:200'],
            'subject' => ['required', 'string', 'max:200'],
            'body'    => ['required', 'string', 'max:2000'],
        ]);

        $contact = Contact::create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        // 管理者へ通知（MAIL_FROM_ADDRESS 宛）
        $adminEmail = config('mail.from.address');
        if ($adminEmail && $adminEmail !== 'hello@example.com') {
            Mail::to($adminEmail)->send(new ContactReceived($contact));
        }

        return redirect()->route('contact.complete');
    }

    public function complete(): View
    {
        return view('contact.complete');
    }
}
