<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Profile extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'languages' => 'array',
            'use_experience_dates' => 'boolean',
        ];
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->profile_image);
    }

    public function getCvFileUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->cv_file);
    }

    public function getWhatsappUrlAttribute(): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $this->phone);

        if (blank($phone)) {
            return null;
        }

        if (Str::startsWith($phone, '0')) {
            $phone = '92'.ltrim($phone, '0');
        }

        return 'https://wa.me/'.$phone;
    }

    public function getGmailComposeUrlAttribute(): ?string
    {
        if (blank($this->email)) {
            return null;
        }

        return 'https://mail.google.com/mail/?view=cm&fs=1&to='.rawurlencode($this->email);
    }

    private function publicFileUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        if (Str::startsWith($path, ['assets/', 'storage/'])) {
            return asset($path);
        }

        return asset('storage/'.$path);
    }
}
