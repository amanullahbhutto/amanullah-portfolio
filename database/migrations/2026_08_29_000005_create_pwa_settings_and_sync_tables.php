<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('pwa_settings')) {
            Schema::create('pwa_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_active')->default(true);
                $table->string('app_name')->default('Amanullah Portfolio & Management');
                $table->string('short_name')->default('Amanullah');
                $table->text('description')->nullable();
                $table->string('theme_color')->default('#070d18');
                $table->string('background_color')->default('#070d18');
                $table->string('display_mode')->default('standalone');
                $table->string('orientation')->default('portrait-primary');
                $table->string('start_url')->default('/admin/dashboard');
                $table->string('scope')->default('/');
                $table->string('install_button_text')->default('Install Application');
                $table->text('offline_message')->nullable();
                $table->text('disabled_message')->nullable();
                $table->text('maintenance_message')->nullable();
                $table->boolean('offline_mode_enabled')->default(true);
                $table->boolean('auto_sync_enabled')->default(true);
                $table->unsignedInteger('max_offline_days')->default(7);
                $table->string('app_version')->default('1.0.0');
                $table->string('app_logo')->nullable();
                $table->string('icon_192')->nullable();
                $table->string('icon_512')->nullable();
                $table->string('icon_maskable')->nullable();
                $table->string('splash_image')->nullable();
                $table->timestamps();
            });

            // Insert initial default PWA configuration
            DB::table('pwa_settings')->insert([
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
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!Schema::hasTable('pwa_sync_logs')) {
            Schema::create('pwa_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->uuid('operation_uuid')->unique();
                $table->string('idempotency_key', 128)->index();
                $table->string('entity', 64)->index();
                $table->string('action', 32); // create, update, delete
                $table->string('status', 32)->default('synced'); // synced, conflict, failed
                $table->json('payload')->nullable();
                $table->unsignedBigInteger('server_id')->nullable()->index();
                $table->string('client_temp_id', 64)->nullable()->index();
                $table->text('error_message')->nullable();
                $table->unsignedInteger('retry_count')->default(0);
                $table->timestamps();
            });
        }

        // Register PWA permissions and assign to Admin roles
        try {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $permissions = [
                'view pwa settings',
                'pwa.view',
                'update pwa settings',
                'pwa.update',
                'manage pwa settings',
                'pwa.manage',
            ];

            foreach ($permissions as $permName) {
                Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            }

            $roles = Role::whereIn('name', ['Super Admin', 'Admin', 'admin'])->get();
            foreach ($roles as $role) {
                $role->givePermissionTo($permissions);
            }
        } catch (\Throwable $e) {
            // Gracefully catch if permissions table has custom id constraints or is missing auto_increment
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pwa_sync_logs');
        Schema::dropIfExists('pwa_settings');

        $permissions = [
            'view pwa settings',
            'pwa.view',
            'update pwa settings',
            'pwa.update',
            'manage pwa settings',
            'pwa.manage',
        ];

        Permission::whereIn('name', $permissions)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

