<?php

namespace App\Providers;

use App\Models\ContactMessage;
use App\Models\Profile;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (request()->isSecure() || request()->header('X-Forwarded-Proto') === 'https' || app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return ($user->hasRole('Super Admin') || $user->hasRole('admin') || $user->hasRole('super-admin')) ? true : null;
        });

        Paginator::useBootstrapFive();

        View::composer('*', function ($view): void {
            $profile = null;
            $unreadMessageCount = 0;
            $pwaSettings = null;

            try {
                if (Schema::hasTable('profiles')) {
                    $profile = Profile::query()->first();
                }

                if (request()->is('admin*') && Schema::hasTable('contact_messages')) {
                    $unreadMessageCount = ContactMessage::query()->unread()->count();
                }

                if (Schema::hasTable('pwa_settings')) {
                    $pwaSettings = \App\Models\PwaSetting::getSettings();
                } else {
                    $pwaSettings = \App\Models\PwaSetting::getFallbackSettings();
                }
            } catch (\Throwable) {
                // Installation pages must still render before migrations complete.
                $pwaSettings = \App\Models\PwaSetting::getFallbackSettings();
            }

            $view->with(compact('profile', 'unreadMessageCount', 'pwaSettings'));
        });
    }
}
