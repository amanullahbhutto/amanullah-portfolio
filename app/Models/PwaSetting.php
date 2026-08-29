<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PwaSetting extends Model
{
    use HasFactory;

    protected $table = 'pwa_settings';

    protected $fillable = [
        'is_active',
        'app_name',
        'short_name',
        'description',
        'theme_color',
        'background_color',
        'display_mode',
        'orientation',
        'start_url',
        'scope',
        'install_button_text',
        'offline_message',
        'disabled_message',
        'maintenance_message',
        'offline_mode_enabled',
        'auto_sync_enabled',
        'max_offline_days',
        'app_version',
        'app_logo',
        'icon_192',
        'icon_512',
        'icon_maskable',
        'splash_image',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'offline_mode_enabled' => 'boolean',
        'auto_sync_enabled' => 'boolean',
        'max_offline_days' => 'integer',
    ];

    public static function getSettings(): self
    {
        return Cache::remember('pwa_app_settings', 3600, function () {
            return self::first() ?? self::createDefault();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('pwa_app_settings');
        Cache::forget('pwa_manifest_json');
    }

    public static function createDefault(): self
    {
        return self::create([
            'is_active' => true,
            'app_name' => 'Amanullah Portfolio & Management',
            'short_name' => 'Amanullah',
            'description' => 'Official Portfolio and Islamic Management System for Amanullah Bhutto.',
            'theme_color' => '#070d18',
            'background_color' => '#070d18',
            'display_mode' => 'standalone',
            'orientation' => 'portrait-primary',
            'start_url' => '/admin/dashboard',
            'scope' => '/',
            'install_button_text' => 'Install Mobile App',
            'offline_message' => 'Aap internet se disconnected hain. Aapka data locally save ho raha hai aur online aate hi sync ho jayega.',
            'disabled_message' => 'Mobile Application ko Administrator ki taraf se waqti taur par deactivate kar diya gaya hai.',
            'maintenance_message' => 'Application par maintenance jari hai. Barah-e-karam thori der baad check karein.',
            'offline_mode_enabled' => true,
            'auto_sync_enabled' => true,
            'max_offline_days' => 7,
            'app_version' => '1.0.0',
            'icon_192' => 'assets/pwa-icons/icon-192x192.png',
            'icon_512' => 'assets/pwa-icons/icon-512x512.png',
            'icon_maskable' => 'assets/pwa-icons/icon-maskable.png',
            'splash_image' => 'assets/pwa-icons/splash-icon.png',
            'app_logo' => 'assets/images/amanullah.png',
        ]);
    }

    public function getIcon192UrlAttribute(): string
    {
        if (!empty($this->icon_192) && file_exists(public_path($this->icon_192))) {
            return asset($this->icon_192);
        }
        return asset('assets/pwa-icons/icon-192x192.png');
    }

    public function getIcon512UrlAttribute(): string
    {
        if (!empty($this->icon_512) && file_exists(public_path($this->icon_512))) {
            return asset($this->icon_512);
        }
        return asset('assets/pwa-icons/icon-512x512.png');
    }

    public function getIconMaskableUrlAttribute(): string
    {
        if (!empty($this->icon_maskable) && file_exists(public_path($this->icon_maskable))) {
            return asset($this->icon_maskable);
        }
        return asset('assets/pwa-icons/icon-maskable.png');
    }

    public function getSplashImageUrlAttribute(): string
    {
        if (!empty($this->splash_image) && file_exists(public_path($this->splash_image))) {
            return asset($this->splash_image);
        }
        return asset('assets/pwa-icons/splash-icon.png');
    }

    public function getAppLogoUrlAttribute(): string
    {
        if (!empty($this->app_logo) && file_exists(public_path($this->app_logo))) {
            return asset($this->app_logo);
        }
        return asset('assets/images/amanullah.png');
    }
}

