<?php

namespace Tests\Feature;

use App\Models\PwaSetting;
use App\Models\Tasbeeh;
use App\Models\User;
use App\Models\UserTasbeehProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PwaSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Setup permissions
        $permissions = [
            'view dashboard', 'manage pwa settings', 'view pwa settings', 'update pwa settings',
            'view zikr', 'update zikr', 'manage tasbeeh',
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $userRole->syncPermissions(Permission::whereIn('name', ['view dashboard', 'view zikr', 'update zikr'])->get());

        $this->admin = User::factory()->create([
            'name' => 'Super Admin User',
            'email' => 'admin@test.com',
        ]);
        $this->admin->assignRole('Super Admin');

        $this->regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
        ]);
        $this->regularUser->assignRole('User');

        PwaSetting::clearCache();
    }

    public function test_dynamic_manifest_returns_valid_json_with_db_settings(): void
    {
        $setting = PwaSetting::getSettings();
        $setting->update([
            'app_name' => 'Test Custom PWA App',
            'short_name' => 'CustomPWA',
            'theme_color' => '#123456',
            'background_color' => '#654321',
        ]);
        PwaSetting::clearCache();

        $response = $this->get(route('pwa.manifest'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json; charset=utf-8')
            ->assertJson([
                'name' => 'Test Custom PWA App',
                'short_name' => 'CustomPWA',
                'theme_color' => '#123456',
                'background_color' => '#654321',
                'display' => 'standalone',
            ]);
    }

    public function test_dynamic_service_worker_script_returns_valid_javascript(): void
    {
        $setting = PwaSetting::getSettings();
        $setting->update(['app_version' => '2.5.0']);
        PwaSetting::clearCache();

        $response = $this->get(route('pwa.sw'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8')
            ->assertSee('portfolio-pwa-v2.5.0')
            ->assertSee('OFFLINE_URL');
    }

    public function test_offline_fallback_page_renders(): void
    {
        $response = $this->get(route('pwa.offline'));

        $response->assertOk()
            ->assertSee('Offline');
    }

    public function test_admin_can_view_pwa_settings(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.pwa.settings'));

        $response->assertOk()
            ->assertSee('Mobile Application Settings')
            ->assertSee('Save Application Settings');
    }

    public function test_non_admin_cannot_access_pwa_settings(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('admin.pwa.settings'));

        $response->assertForbidden();
    }

    public function test_admin_can_update_pwa_settings(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('admin.pwa.settings.update'), [
            'is_active' => '1',
            'app_name' => 'Updated App Name',
            'short_name' => 'UpdatedShort',
            'description' => 'Updated Description',
            'theme_color' => '#0f172a',
            'background_color' => '#0f172a',
            'display_mode' => 'standalone',
            'orientation' => 'portrait-primary',
            'start_url' => '/admin/dashboard',
            'install_button_text' => 'Install Updated App',
            'offline_message' => 'Custom offline msg',
            'disabled_message' => 'Custom disabled msg',
            'maintenance_message' => 'Custom maint msg',
            'offline_mode_enabled' => '1',
            'auto_sync_enabled' => '1',
            'max_offline_days' => 14,
            'app_version' => '1.1.0',
        ]);

        $response->assertRedirect(route('admin.pwa.settings'));

        $setting = PwaSetting::first();
        $this->assertSame('Updated App Name', $setting->app_name);
        $this->assertSame('UpdatedShort', $setting->short_name);
        $this->assertSame(14, $setting->max_offline_days);
        $this->assertSame('1.1.0', $setting->app_version);
    }

    public function test_admin_can_toggle_pwa_active_status(): void
    {
        $this->actingAs($this->admin);

        $setting = PwaSetting::getSettings();
        $this->assertTrue((bool) $setting->is_active);

        // Toggle to inactive
        $res1 = $this->postJson(route('admin.pwa.toggle-active'));
        $res1->assertOk()->assertJson(['success' => true, 'is_active' => false]);
        $this->assertFalse((bool) $setting->fresh()->is_active);

        // Toggle back to active
        $res2 = $this->postJson(route('admin.pwa.toggle-active'));
        $res2->assertOk()->assertJson(['success' => true, 'is_active' => true]);
        $this->assertTrue((bool) $setting->fresh()->is_active);
    }

    public function test_pwa_status_endpoint_returns_json(): void
    {
        $response = $this->getJson(route('pwa.status'));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'is_active',
                'app_version',
                'app_name',
                'short_name',
                'messages',
                'server_time',
            ]);
    }

    public function test_pwa_push_sync_creates_record_and_prevents_duplicates_via_idempotency(): void
    {
        $this->actingAs($this->regularUser);

        $tasbeeh = Tasbeeh::create([
            'title' => 'Sync Test Tasbeeh',
            'arabic_text' => 'سُبْحَانَ اللَّهِ',
            'daily_target' => 100,
            'is_active' => true,
        ]);

        $opUuid = (string) Str::uuid();

        $payload = [
            'operations' => [
                [
                    'uuid' => $opUuid,
                    'idempotency_key' => $opUuid,
                    'entity' => 'tasbeeh_count',
                    'action' => 'create',
                    'temp_id' => 'temp_123',
                    'payload' => [
                        'tasbeeh_id' => $tasbeeh->id,
                        'count' => 33,
                    ],
                ],
            ],
        ];

        // 1st Push -> Success
        $res1 = $this->postJson(route('pwa.sync.push'), $payload);
        $res1->assertOk()->assertJson(['success' => true]);

        $progress = UserTasbeehProgress::where('user_id', $this->regularUser->id)
            ->where('tasbeeh_id', $tasbeeh->id)
            ->first();
        $this->assertSame(33, (int) $progress->total_completed);

        // 2nd Push with SAME operation UUID -> Idempotency kicks in, no double increment!
        $res2 = $this->postJson(route('pwa.sync.push'), $payload);
        $res2->assertOk();

        $this->assertSame(33, (int) $progress->fresh()->total_completed);
    }

    public function test_pwa_pull_sync_returns_delta_data(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->getJson(route('pwa.sync.pull'));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'app_active',
                'server_time',
                'user',
                'data' => [
                    'tasbeehs',
                    'zikr_summary',
                ],
            ]);
    }

    public function test_pwa_push_sync_blocked_when_app_is_deactivated(): void
    {
        $this->actingAs($this->regularUser);

        $setting = PwaSetting::getSettings();
        $setting->update(['is_active' => false]);
        PwaSetting::clearCache();

        $payload = [
            'operations' => [
                [
                    'uuid' => (string) Str::uuid(),
                    'entity' => 'tasbeeh_count',
                    'action' => 'create',
                    'payload' => ['count' => 10],
                ],
            ],
        ];

        $response = $this->postJson(route('pwa.sync.push'), $payload);

        $response->assertOk()
            ->assertJson([
                'success' => false,
                'app_active' => false,
            ]);
    }
}
