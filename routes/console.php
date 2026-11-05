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
        $this->info("SUCCESS (Laravel Mailer): Email dispatched to {$targetEmail}!");
    } catch (\Throwable $e) {
        $this->warn("Laravel Mailer note: " . $e->getMessage());
    }

    if (function_exists('mail')) {
        $subject = 'Test PHP mail() function: Hire Amanullah';
        $body = "This is a test using PHP built-in mail() function to verify server email delivery to {$targetEmail}.";
        $fromEmail = config('mail.from.address') ?: 'amanullah@triplewtools.com';
        $headers = "From: Amanullah Portfolio <{$fromEmail}>\r\nReply-To: client@example.com\r\nX-Mailer: PHP/" . phpversion();
        $phpMailResult = @mail($targetEmail, $subject, $body, $headers);
        if ($phpMailResult) {
            $this->info("SUCCESS (PHP mail): Native mail() function accepted the email for delivery to {$targetEmail}!");
        } else {
            $this->comment("PHP mail() function did not send locally (normal on Windows local without mail server, but works on live cPanel/Linux).");
        }
    }
})->purpose('Test sending a portfolio contact email');

Artisan::command('zikr:fix-ayahs', function () {
    $this->info("Scanning and verifying Quranic Ayah formatting across database...");

    $tasbeehs = \App\Models\Tasbeeh::all();
    $tCount = 0;
    foreach ($tasbeehs as $t) {
        $raw = (string) $t->arabic_text;
        if (str_contains($raw, 'قُلْ هُوَ اللّٰهُ أَحَدٌ') || str_contains($raw, 'قل هو الله') || stripos($t->title, 'ikhlas') !== false) {
            $t->arabic_text = 'قُلْ هُوَ اللّٰهُ أَحَدٌ ۝١ اللّٰهُ الصَّمَدُ ۝٢ لَمْ يَلِدْ وَلَمْ يُولَدْ ۝٣ وَلَمْ يَكُن لَّهُ كُفُوًا أَحَدٌ ۝٤';
            $t->urdu_meaning = 'کہہ دیجئے: وہ اللہ ایک ہے۔ اللہ بے نیاز ہے۔ نہ اس نے کسی کو جنا اور نہ وہ جنا گیا۔ اور کوئی اس کا ہمسر نہیں ہے۔';
            $t->save();
            $tCount++;
            $this->info("Updated Surah Al-Ikhlas (Tasbeeh ID: {$t->id}) with verified Quranic ayah symbols.");
            continue;
        }
        $clean = (string) preg_replace('/[ ]{2,}/u', ' ', trim($raw));
        if ($clean !== $t->arabic_text) {
            $t->arabic_text = $clean;
            $t->save();
            $tCount++;
        }
    }

    $duas = \App\Models\Dua::all();
    $dCount = 0;
    foreach ($duas as $d) {
        $clean = (string) preg_replace('/[ ]{2,}/u', ' ', trim((string) $d->arabic_text));
        if ($clean !== $d->arabic_text) {
            $d->arabic_text = $clean;
            $d->save();
            $dCount++;
        }
    }

    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');

    $this->info("View cache and application cache cleared successfully!");
    $this->info("Ayahs verified! Total Tasbeehs: {$tasbeehs->count()} | Total Duas: {$duas->count()}");
})->purpose('Cleans and verifies Ayah formatting in Zikr & Duas and clears view cache');
