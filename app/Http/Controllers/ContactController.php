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

        $recipient = config('portfolio.contact_notification_email') ?: 'aman.ullah.csc@gmail.com';

        $sent = false;

        // Agar mailer 'mail' par set hai to direct PHP native mail() use karein
        if (config('mail.default') === 'mail') {
            $sent = $this->sendViaPhpMail($recipient, $message);
        } else {
            try {
                Mail::to($recipient)
                    ->send(new ContactMessageReceived($message));
                $sent = true;
            } catch (Throwable $exception) {
                report($exception);
            }

            // Agar SMTP / Laravel Mailer fail ho jaye ya log par ho, to simple PHP mail() se backup send karein
            if (! $sent || config('mail.default') === 'log') {
                try {
                    $this->sendViaPhpMail($recipient, $message);
                } catch (Throwable $fallbackException) {
                    report($fallbackException);
                }
            }
        }

        return back()->with([
            'success' => 'Thank you for submitting the form. Amanullah will contact you as soon as possible.',
            'flash_title' => 'Message received',
            'flash_duration' => 4000,
            'flash_variant' => 'contact-success-popup',
        ]);
    }

    protected function sendViaPhpMail(string $recipient, ContactMessage $message): bool
    {
        if (! function_exists('mail')) {
            return false;
        }

        $subject = 'New portfolio contact: ' . $message->subject;
        $body = view('emails.contact-message-received', ['contactMessage' => $message])->render();

        $fromAddress = config('mail.from.address') ?: 'amanullah@triplewtools.com';
        $fromName = config('mail.from.name') ?: 'Amanullah Portfolio';

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $fromName . ' <' . $fromAddress . '>',
            'Reply-To: ' . $message->name . ' <' . $message->email . '>',
            'X-Mailer: PHP/' . phpversion(),
        ];

        return (bool) @mail($recipient, $subject, $body, implode("\r\n", $headers));
    }
}
