<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('public.contact', [
            'profile' => \App\Models\Profile::query()->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['nullable', 'max:0'],
        ]);

        unset($validated['website']);
        $validated['ip_address'] = $request->ip();
        $message = ContactMessage::query()->create($validated);

        try {
            Mail::to(config('portfolio.contact_notification_email'))
                ->send(new ContactMessageReceived($message));
        } catch (Throwable $exception) {
            report($exception);
        }

        return back()->with([
            'success' => 'Thank you for submitting the form. Amanullah will contact you as soon as possible.',
            'flash_title' => 'Message received',
            'flash_duration' => 4000,
            'flash_variant' => 'contact-success-popup',
        ]);
    }
}
