<?php

namespace Tests\Feature;

use App\Models\NamazAttendance;
use App\Models\NamazSetting;
use App\Models\User;
use App\Services\NamazAttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NamazAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Role $superAdminRole;
    protected Role $muslimRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $this->muslimRole = Role::firstOrCreate(['name' => 'Muslim', 'guard_name' => 'web']);

        $permissions = [
            'view namaz attendance',
            'namaz_attendance.view',
            'create namaz attendance',
            'namaz_attendance.create',
            'update namaz attendance',
            'namaz_attendance.update',
            'delete namaz attendance',
            'namaz_attendance.delete',
            'view namaz dashboard',
            'namaz_dashboard.view',
            'view namaz settings',
            'namaz_settings.view',
            'update namaz settings',
            'namaz_settings.update',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $this->superAdminRole->syncPermissions($permissions);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole($this->superAdminRole);
    }

    public function test_admin_can_view_namaz_attendance_index(): void
    {
        $this->actingAs($this->admin);

        $muslim = User::create([
            'name' => 'Ahmed Khan',
            'email' => 'ahmed@example.com',
            'password' => bcrypt('password'),
            'namaz_start_date' => '2026-08-01',
        ]);
        $muslim->assignRole($this->muslimRole);

        $response = $this->get(route('admin.namaz.attendance.index'));
        $response->assertOk();
        $response->assertSee('Namaz Attendance');
        $response->assertSee('Ahmed Khan');
    }

    public function test_non_muslim_users_are_excluded_from_namaz_attendance(): void
    {
        $this->actingAs($this->admin);

        $nonMuslim = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->get(route('admin.namaz.attendance.index'));
        $response->assertOk();
        $response->assertDontSee('John Doe (');
    }

    public function test_muslim_user_without_start_date_shows_empty_state_and_can_set_start_date(): void
    {
        $this->actingAs($this->admin);

        $muslim = User::create([
            'name' => 'Bilal Ahmed',
            'email' => 'bilal@example.com',
            'password' => bcrypt('password'),
            'namaz_start_date' => null,
        ]);
        $muslim->assignRole($this->muslimRole);

        $response = $this->get(route('admin.namaz.attendance.index', ['user_id' => $muslim->id]));
        $response->assertOk();
        $response->assertSee('Namaz attendance has not been started for Bilal Ahmed');

        // Set start date via AJAX
        $postResponse = $this->postJson(route('admin.namaz.attendance.start-date', $muslim), [
            'namaz_start_date' => '2026-08-25',
        ]);

        $postResponse->assertOk();
        $postResponse->assertJsonFragment(['success' => true]);

        $this->assertSame('2026-08-25', $muslim->fresh()->namaz_start_date->format('Y-m-d'));
    }

    public function test_attendance_generation_starts_from_namaz_start_date(): void
    {
        $service = app(NamazAttendanceService::class);

        $muslim = User::create([
            'name' => 'Tariq Ali',
            'email' => 'tariq@example.com',
            'password' => bcrypt('password'),
            'namaz_start_date' => '2026-08-25',
        ]);
        $muslim->assignRole($this->muslimRole);

        // Date before start date -> not_applicable
        $statusBefore = $service->getEffectivePrayerStatus($muslim, '2026-08-24', 'fajr');
        $this->assertSame(NamazAttendanceService::STATUS_NOT_APPLICABLE, $statusBefore['key']);

        // Date on start date -> past date auto kaza
        $statusOn = $service->getEffectivePrayerStatus($muslim, '2026-08-25', 'fajr');
        $this->assertSame(NamazAttendanceService::STATUS_KAZA, $statusOn['key']);
    }

    public function test_friday_zuhr_displays_as_jummah_and_uses_jummah_time(): void
    {
        $service = app(NamazAttendanceService::class);
        $setting = NamazSetting::getSettings();
        $setting->update([
            'zuhr_time' => '13:15',
            'jummah_time' => '13:45',
        ]);

        $friday = '2026-08-28'; // Friday
        $thursday = '2026-08-27'; // Thursday

        $this->assertSame("Jumu'ah", $service->getPrayerLabel('zuhr', $friday));
        $this->assertSame("Zuhr", $service->getPrayerLabel('zuhr', $thursday));

        $this->assertSame('13:45', $setting->getTimeForPrayer('zuhr', $friday));
        $this->assertSame('13:15', $setting->getTimeForPrayer('zuhr', $thursday));
    }

    public function test_admin_can_update_single_prayer_status_via_ajax(): void
    {
        $this->actingAs($this->admin);

        $muslim = User::create([
            'name' => 'Usman Ghani',
            'email' => 'usman@example.com',
            'password' => bcrypt('password'),
            'namaz_start_date' => '2026-08-01',
        ]);
        $muslim->assignRole($this->muslimRole);

        $response = $this->postJson(route('admin.namaz.attendance.status'), [
            'user_id' => $muslim->id,
            'attendance_date' => '2026-08-27',
            'prayer' => 'fajr',
            'status' => 'jamat',
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $attendance = NamazAttendance::where('user_id', $muslim->id)->whereDate('attendance_date', '2026-08-27')->first();
        $this->assertNotNull($attendance);
        $this->assertSame('jamat', $attendance->fajr_status);

        // Reset status
        $resetResponse = $this->postJson(route('admin.namaz.attendance.status'), [
            'user_id' => $muslim->id,
            'attendance_date' => '2026-08-27',
            'prayer' => 'fajr',
            'status' => null,
        ]);

        $resetResponse->assertOk();
    }

    public function test_cannot_mark_attendance_before_prayer_time_arrives(): void
    {
        $this->actingAs($this->admin);

        $muslim = User::create([
            'name' => 'Kashif Ali',
            'email' => 'kashif@example.com',
            'password' => bcrypt('password'),
            'namaz_start_date' => '2026-08-01',
        ]);
        $muslim->assignRole($this->muslimRole);

        // Future date: 2099-01-01
        $response = $this->postJson(route('admin.namaz.attendance.status'), [
            'user_id' => $muslim->id,
            'attendance_date' => '2099-01-01',
            'prayer' => 'fajr',
            'status' => 'jamat',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['success' => false]);
    }

    public function test_manual_status_takes_priority_over_auto_kaza_and_pending(): void
    {
        $service = app(NamazAttendanceService::class);

        $muslim = User::create([
            'name' => 'Farhan Qureshi',
            'email' => 'farhan@example.com',
            'password' => bcrypt('password'),
            'namaz_start_date' => '2026-08-01',
        ]);
        $muslim->assignRole($this->muslimRole);

        // Past date unmarked -> kaza
        $autoKaza = $service->getEffectivePrayerStatus($muslim, '2026-08-20', 'fajr', null);
        $this->assertSame(NamazAttendanceService::STATUS_KAZA, $autoKaza['key']);
        $this->assertFalse($autoKaza['is_manual']);

        // Past date marked jamat -> jamat
        $manualJamat = $service->getEffectivePrayerStatus($muslim, '2026-08-20', 'fajr', 'jamat');
        $this->assertSame(NamazAttendanceService::STATUS_JAMAT, $manualJamat['key']);
        $this->assertTrue($manualJamat['is_manual']);

        // Past date marked absent -> absent
        $manualAbsent = $service->getEffectivePrayerStatus($muslim, '2026-08-20', 'fajr', 'absent');
        $this->assertSame(NamazAttendanceService::STATUS_ABSENT, $manualAbsent['key']);
        $this->assertTrue($manualAbsent['is_manual']);
    }

    public function test_admin_can_update_full_day_attendance_and_reset(): void
    {
        $this->actingAs($this->admin);

        $muslim = User::create([
            'name' => 'Salman Tariq',
            'email' => 'salman@example.com',
            'password' => bcrypt('password'),
            'namaz_start_date' => '2026-08-01',
        ]);
        $muslim->assignRole($this->muslimRole);

        $response = $this->postJson(route('admin.namaz.attendance.day'), [
            'user_id' => $muslim->id,
            'attendance_date' => '2026-08-27',
            'fajr_status' => 'jamat',
            'zuhr_status' => 'without_jamat',
            'asr_status' => 'jamat',
            'maghrib_status' => 'kaza',
            'isha_status' => 'absent',
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $attendance = NamazAttendance::where('user_id', $muslim->id)->whereDate('attendance_date', '2026-08-27')->first();
        $this->assertNotNull($attendance);
        $this->assertSame('jamat', $attendance->fajr_status);
        $this->assertSame('without_jamat', $attendance->zuhr_status);
        $this->assertSame('jamat', $attendance->asr_status);
        $this->assertSame('kaza', $attendance->maghrib_status);
        $this->assertSame('absent', $attendance->isha_status);

        // Delete / Reset
        $deleteResponse = $this->deleteJson(route('admin.namaz.attendance.destroy', $attendance));
        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('namaz_attendances', ['id' => $attendance->id]);
    }

    public function test_admin_can_view_namaz_dashboard_with_statistics(): void
    {
        $this->actingAs($this->admin);

        $muslim = User::create([
            'name' => 'Rashid Mehmood',
            'email' => 'rashid@example.com',
            'password' => bcrypt('password'),
            'namaz_start_date' => '2026-08-01',
        ]);
        $muslim->assignRole($this->muslimRole);

        NamazAttendance::create([
            'user_id' => $muslim->id,
            'attendance_date' => '2026-08-10',
            'fajr_status' => 'jamat',
            'zuhr_status' => 'jamat',
            'asr_status' => 'without_jamat',
            'maghrib_status' => 'kaza',
            'isha_status' => 'absent',
        ]);

        $response = $this->get(route('admin.namaz.dashboard.index', ['user_id' => $muslim->id]));
        $response->assertOk();
        $response->assertSee('Namaz Attendance Dashboard');
        $response->assertSee('Rashid Mehmood');
        $response->assertSee('Per-Prayer Attendance Breakdown');
    }

    public function test_admin_can_view_and_update_namaz_settings(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.namaz.settings.index'));
        $response->assertOk();
        $response->assertSee('Namaz Prayer Timings');

        $updateResponse = $this->postJson(route('admin.namaz.settings.update'), [
            'fajr_time' => '05:15',
            'zuhr_time' => '13:30',
            'asr_time' => '17:00',
            'maghrib_time' => '19:00',
            'isha_time' => '20:30',
            'jummah_time' => '13:45',
        ]);

        $updateResponse->assertOk();
        $updateResponse->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('namaz_settings', [
            'fajr_time' => '05:15',
            'jummah_time' => '13:45',
        ]);
    }

    public function test_user_without_permissions_is_forbidden(): void
    {
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($regularUser);

        $response = $this->get(route('admin.namaz.attendance.index'));
        $response->assertForbidden();

        $dashboardResponse = $this->get(route('admin.namaz.dashboard.index'));
        $dashboardResponse->assertForbidden();

        $settingsResponse = $this->get(route('admin.namaz.settings.index'));
        $settingsResponse->assertForbidden();
    }
}
