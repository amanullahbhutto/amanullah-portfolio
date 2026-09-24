<?php

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('portfolio:about', function () {
    $this->comment('Amanullah - PHP & Laravel Developer Portfolio');
})->purpose('Display portfolio information');

Artisan::command('mail:test {email?}', function (?string $email = null) {
    $targetEmail = $email ?: (config('portfolio.contact_notification_email') ?: 'aman.ullah.csc@gmail.com');
    $this->info("Attempting to send test email to: {$targetEmail}");
    $this->comment("Current Mailer: " . config('mail.default'));
    $this->comment("SMTP Host: " . config('mail.mailers.smtp.host'));
    $this->comment("SMTP Port: " . config('mail.mailers.smtp.port'));
    $this->comment("SMTP Username: " . config('mail.mailers.smtp.username'));

    $dummyMessage = new ContactMessage([
        'name' => 'Portfolio Test Client',
        'email' => 'client@example.com',
        'phone' => '+92 318 3588065',
        'subject' => 'Hire Me Inquiry (Test)',
        'message' => 'Hello Amanullah, I would like to hire you for a Laravel project. Please get in touch!',
        'ip_address' => '127.0.0.1',
    ]);
    $dummyMessage->id = 1;

    try {
        Mail::to($targetEmail)->send(new ContactMessageReceived($dummyMessage));
        $this->info("SUCCESS: Test email sent successfully to {$targetEmail}!");
    } catch (\Throwable $e) {
        $this->error("FAILED: " . $e->getMessage());
    }
})->purpose('Test sending a portfolio contact email');

