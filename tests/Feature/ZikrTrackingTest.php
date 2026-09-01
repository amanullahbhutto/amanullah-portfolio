<?php

namespace Tests\Feature;

use App\Models\Tasbeeh;
use App\Models\User;
use App\Models\UserTasbeehProgress;
use App\Services\ZikrService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ZikrTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $muslimUser;
    protected User $nonMuslimUser;
    protected Role $adminRole;
    protected Role $muslimRole;
    protected Tasbeeh $tasbeeh;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $this->muslimRole = Role::firstOrCreate(['name' => 'Muslim', 'guard_name' => 'web']);

        $permissions = [
            'view zikr',
            'zikr.view',
            'create zikr',
            'zikr.create',
            'update zikr',
            'zikr.update',
            'reset zikr',
            'zikr.reset',
            'manage tasbeeh',
            'tasbeeh.manage',
            'delete tasbeeh',
            'tasbeeh.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $this->adminRole->givePermissionTo($permissions);
        $this->muslimRole->givePermissionTo([
            'view zikr',
            'zikr.view',
            'create zikr',
            'zikr.create',
            'update zikr',
            'zikr.update',
            'reset zikr',
            'zikr.reset',
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole($this->adminRole);
        $this->admin->assignRole($this->muslimRole);

        $this->muslimUser = User::create([
            'name' => 'Amanullah',
            'email' => 'amanullah@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->muslimUser->assignRole($this->muslimRole);

        $this->nonMuslimUser = User::create([
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->tasbeeh = Tasbeeh::create([
            'title' => 'Tasbeeh-e-Fatima',
            'arabic_text' => 'سُبْحَانَ اللهِ، وَالْحَمْدُ لِلّٰهِ',
            'urdu_meaning' => 'اللہ پاک ہے، تمام تعریفیں اللہ کے لیے ہیں۔',
            'daily_target' => 100,
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_muslim_user_can_view_zikr_dashboard(): void
    {
        $this->actingAs($this->muslimUser);

        $response = $this->get(route('admin.zikr.index'));
        $response->assertOk();
        $response->assertSee('Daily Zikr Tracking');
        $response->assertSee('Tasbeeh-e-Fatima');
        $response->assertSee('Read Today');
    }

    public function test_non_muslim_user_is_forbidden_from_zikr(): void
    {
        $this->actingAs($this->nonMuslimUser);

        $response = $this->get(route('admin.zikr.index'));
        $response->assertForbidden();

        $counterResponse = $this->get(route('admin.zikr.counter.show', $this->tasbeeh));
        $counterResponse->assertForbidden();

        $incrementResponse = $this->postJson(route('admin.zikr.counter.increment', $this->tasbeeh), ['count' => 1]);
        $incrementResponse->assertForbidden();
    }

    public function test_daily_target_and_cumulative_backlog_calculation(): void
    {
        $service = app(ZikrService::class);
        $tz = $service->getTimezone();

        // 10 active days (e.g. Started 10 days ago)
        $tenDaysAgo = Carbon::now($tz)->subDays(9)->format('Y-m-d'); // 9 subDays + today = 10 days inclusive

        $progress = UserTasbeehProgress::create([
            'user_id' => $this->muslimUser->id,
            'tasbeeh_id' => $this->tasbeeh->id,
            'total_completed' => 700,
            'tracking_start_date' => $tenDaysAgo,
        ]);

        $stats = $service->calculateTasbeehStats($this->muslimUser, $this->tasbeeh, $progress);

        $this->assertSame(10, $stats['active_days']);
        $this->assertSame(1000, $stats['total_required']); // 10 * 100
        $this->assertSame(700, $stats['total_completed']);
        $this->assertSame(300, $stats['remaining']); // 1000 - 700 = 300
        $this->assertSame(0, $stats['extra']);
        $this->assertSame(0, $stats['today_completed']);
        $this->assertEquals(70, $stats['percentage']);
    }

    public function test_extra_zikr_calculation(): void
    {
        $service = app(ZikrService::class);
        $tz = $service->getTimezone();

        $threeDaysAgo = Carbon::now($tz)->subDays(2)->format('Y-m-d'); // 3 days inclusive

        $progress = UserTasbeehProgress::create([
            'user_id' => $this->muslimUser->id,
            'tasbeeh_id' => $this->tasbeeh->id,
            'total_completed' => 450,
            'tracking_start_date' => $threeDaysAgo,
        ]);

        $stats = $service->calculateTasbeehStats($this->muslimUser, $this->tasbeeh, $progress);

        $this->assertSame(3, $stats['active_days']);
        $this->assertSame(300, $stats['total_required']); // 3 * 100
        $this->assertSame(450, $stats['total_completed']);
        $this->assertSame(0, $stats['remaining']);
        $this->assertSame(150, $stats['extra']); // 450 - 300 = 150
        $this->assertEquals(100, $stats['percentage']);
    }

    public function test_live_counter_increment_updates_single_progress_record(): void
    {
        $this->actingAs($this->muslimUser);

        $response = $this->postJson(route('admin.zikr.counter.increment', $this->tasbeeh), [
            'count' => 1,
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $progress = UserTasbeehProgress::where('user_id', $this->muslimUser->id)
            ->where('tasbeeh_id', $this->tasbeeh->id)
            ->first();

        $this->assertNotNull($progress);
        $this->assertSame(1, $progress->total_completed);
        $this->assertNotNull($progress->last_zikr_at);

        // Subsequent increment updates the exact same row (no new rows created!)
        $this->postJson(route('admin.zikr.counter.increment', $this->tasbeeh), ['count' => 5]);

        $this->assertSame(1, UserTasbeehProgress::where('user_id', $this->muslimUser->id)->where('tasbeeh_id', $this->tasbeeh->id)->count());
        $this->assertSame(6, $progress->fresh()->total_completed);
    }

    public function test_manual_zikr_count_addition(): void
    {
        $this->actingAs($this->muslimUser);

        $response = $this->postJson(route('admin.zikr.counter.manual', $this->tasbeeh), [
            'count' => 200,
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $progress = UserTasbeehProgress::where('user_id', $this->muslimUser->id)
            ->where('tasbeeh_id', $this->tasbeeh->id)
            ->first();

        $this->assertSame(200, $progress->total_completed);

        // Allows negative counts for adjustment (e.g. -50 decreases total to 150)
        $negResponse = $this->postJson(route('admin.zikr.counter.manual', $this->tasbeeh), ['count' => -50]);
        $negResponse->assertOk();
        $this->assertSame(150, $progress->fresh()->total_completed);

        // Rejects 0 count
        $badResponse = $this->postJson(route('admin.zikr.counter.manual', $this->tasbeeh), ['count' => 0]);
        $badResponse->assertStatus(422);
    }

    public function test_reset_progress_resets_only_selected_tasbeeh(): void
    {
        $this->actingAs($this->muslimUser);

        $tasbeehTwo = Tasbeeh::create([
            'title' => 'Darood Shareef',
            'arabic_text' => 'اللَّهُمَّ صَلِّ عَلَىٰ مُحَمَّدٍ',
            'urdu_meaning' => 'اے اللہ محمد پر رحمت نازل فرما۔',
            'daily_target' => 100,
            'is_active' => true,
        ]);

        // User has completed 3000 on Tasbeeh 1 and 2100 on Tasbeeh 2
        $p1 = UserTasbeehProgress::create([
            'user_id' => $this->muslimUser->id,
            'tasbeeh_id' => $this->tasbeeh->id,
            'total_completed' => 3000,
            'tracking_start_date' => '2026-08-01',
        ]);

        $p2 = UserTasbeehProgress::create([
            'user_id' => $this->muslimUser->id,
            'tasbeeh_id' => $tasbeehTwo->id,
            'total_completed' => 2100,
            'tracking_start_date' => '2026-08-01',
        ]);

        // Reset Tasbeeh 1
        $response = $this->postJson(route('admin.zikr.counter.reset', $this->tasbeeh));
        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        // Tasbeeh 1 is reset to 0 with today's date
        $p1->refresh();
        $this->assertSame(0, $p1->total_completed);
        $this->assertSame(now()->format('Y-m-d'), $p1->tracking_start_date->format('Y-m-d'));
        $this->assertNull($p1->last_zikr_at);

        // Tasbeeh 2 MUST remain completely untouched!
        $p2->refresh();
        $this->assertSame(2100, $p2->total_completed);
        $this->assertSame('2026-08-01', $p2->tracking_start_date->format('Y-m-d'));
    }

    public function test_admin_can_manage_tasbeeh_master_definitions(): void
    {
        $this->actingAs($this->admin);

        // Index
        $this->get(route('admin.tasbeehs.index'))->assertOk();

        // Store
        $storeResponse = $this->postJson(route('admin.tasbeehs.store'), [
            'title' => 'Ayat-e-Shifa',
            'arabic_text' => 'وَيَشْفِ صُدُورَ قَوْمٍ مُؤْمِنِينَ',
            'urdu_meaning' => 'اور وہ مومنوں کے سینوں کو شفا دیتا ہے۔',
            'daily_target' => 70,
            'sort_order' => 10,
        ]);
        $storeResponse->assertOk();
        $this->assertDatabaseHas('tasbeehs', ['title' => 'Ayat-e-Shifa', 'daily_target' => 70]);

        $created = Tasbeeh::where('title', 'Ayat-e-Shifa')->first();

        // Update
        $this->putJson(route('admin.tasbeehs.update', $created), [
            'title' => 'Ayat-e-Shifa Updated',
            'arabic_text' => 'وَيَشْفِ صُدُورَ قَوْمٍ مُؤْمِنِينَ',
            'urdu_meaning' => 'شفا کی آیت',
            'daily_target' => 100,
        ])->assertOk();

        $this->assertSame('Ayat-e-Shifa Updated', $created->fresh()->title);

        // Toggle
        $this->patchJson(route('admin.tasbeehs.toggle', $created))->assertOk();
        $this->assertFalse($created->fresh()->is_active);

        // Destroy
        $this->deleteJson(route('admin.tasbeehs.destroy', $created))->assertOk();
        $this->assertDatabaseMissing('tasbeehs', ['id' => $created->id]);
    }

    public function test_can_complete_all_tasbeehs_for_today(): void
    {
        $user = $this->muslimUser;
        $this->actingAs($user);

        $t1 = Tasbeeh::create([
            'title' => 'Tasbeeh 1',
            'arabic_text' => 'سُبْحَانَ اللَّهِ',
            'daily_target' => 100,
            'is_active' => true,
        ]);
        $t2 = Tasbeeh::create([
            'title' => 'Tasbeeh 2',
            'arabic_text' => 'الْحَمْدُ لِلَّهِ',
            'daily_target' => 300,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('admin.zikr.complete-all-today'), [
            'user_id' => $user->id,
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $p1 = UserTasbeehProgress::where('user_id', $user->id)->where('tasbeeh_id', $t1->id)->first();
        $p2 = UserTasbeehProgress::where('user_id', $user->id)->where('tasbeeh_id', $t2->id)->first();

        $this->assertSame(100, (int) $p1->total_completed);
        $this->assertSame(300, (int) $p2->total_completed);
    }

    public function test_can_reset_all_tasbeehs(): void
    {
        $user = $this->muslimUser;
        $this->actingAs($user);

        $t1 = Tasbeeh::create([
            'title' => 'Tasbeeh 1',
            'arabic_text' => 'سُبْحَانَ اللَّهِ',
            'daily_target' => 100,
            'is_active' => true,
        ]);

        // Add some count first
        $this->postJson(route('admin.zikr.counter.increment', $t1), ['count' => 50])->assertOk();

        // Reset all
        $response = $this->postJson(route('admin.zikr.reset-all'), [
            'user_id' => $user->id,
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $p1 = UserTasbeehProgress::where('user_id', $user->id)->where('tasbeeh_id', $t1->id)->first();
        $this->assertSame(0, (int) $p1->total_completed);
        $this->assertSame(now()->format('Y-m-d'), $p1->tracking_start_date?->format('Y-m-d'));
    }

    public function test_lifetime_zikr_counter_persists_across_resets_and_deletions(): void
    {
        $user = $this->muslimUser;
        $this->actingAs($user);

        $t1 = Tasbeeh::create([
            'title' => 'Lifetime Test Tasbeeh',
            'arabic_text' => 'سُبْحَانَ اللَّهِ',
            'daily_target' => 100,
            'is_active' => true,
        ]);

        // Increment 75
        $this->postJson(route('admin.zikr.counter.increment', $t1), ['count' => 75])->assertOk();

        $lifetime = \App\Models\UserLifetimeZikr::where('user_id', $user->id)->first();
        $this->assertNotNull($lifetime);
        $this->assertSame(75, (int) $lifetime->lifetime_count);

        // Reset all active tracking
        $this->postJson(route('admin.zikr.reset-all'), ['user_id' => $user->id])->assertOk();

        // Active cycle progress is 0, but Lifetime count is STILL 75!
        $this->assertSame(75, (int) $lifetime->fresh()->lifetime_count);

        // Delete the master Tasbeeh
        $t1->delete();

        // Lifetime count STILL persists after Tasbeeh deletion!
        $this->assertSame(75, (int) $lifetime->fresh()->lifetime_count);

        // Explicit reset of lifetime counter
        $this->postJson(route('admin.zikr.reset-lifetime'), ['user_id' => $user->id])->assertOk();
        $this->assertSame(0, (int) $lifetime->fresh()->lifetime_count);
    }

    public function test_can_complete_single_tasbeeh_for_today(): void
    {
        $user = $this->muslimUser;
        $this->actingAs($user);

        $t1 = Tasbeeh::create([
            'title' => 'Single Complete Tasbeeh 1',
            'arabic_text' => 'سُبْحَانَ اللَّهِ',
            'daily_target' => 100,
            'is_active' => true,
        ]);

        $t2 = Tasbeeh::create([
            'title' => 'Single Complete Tasbeeh 2',
            'arabic_text' => 'الْحَمْدُ لِلَّهِ',
            'daily_target' => 200,
            'is_active' => true,
        ]);

        // Complete ONLY t1 for today
        $response = $this->postJson(route('admin.zikr.counter.complete-today', $t1), [
            'user_id' => $user->id,
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $p1 = UserTasbeehProgress::where('user_id', $user->id)->where('tasbeeh_id', $t1->id)->first();
        $p2 = UserTasbeehProgress::where('user_id', $user->id)->where('tasbeeh_id', $t2->id)->first();

        // T1 is 100 completed
        $this->assertSame(100, (int) $p1->total_completed);

        // T2 is NOT completed (still 0 or null)
        $this->assertTrue(is_null($p2) || (int) $p2->total_completed === 0);

        // Lifetime counter increased by 100
        $lifetime = \App\Models\UserLifetimeZikr::where('user_id', $user->id)->first();
        $this->assertNotNull($lifetime);
        $this->assertSame(100, (int) $lifetime->lifetime_count);

        // Clicking complete-today again when already completed does NOT add extra count
        $secondResponse = $this->postJson(route('admin.zikr.counter.complete-today', $t1), [
            'user_id' => $user->id,
        ]);
        $secondResponse->assertOk()->assertJson(['already_completed' => true]);
        $this->assertSame(100, (int) $p1->fresh()->total_completed);
    }

    public function test_complete_today_advances_day_by_day_when_backlog_exists(): void
    {
        $user = $this->muslimUser;
        $this->actingAs($user);

        $t = Tasbeeh::create([
            'title' => 'Backlog Day-by-Day Tasbeeh',
            'arabic_text' => 'سُبْحَانَ اللَّهِ',
            'daily_target' => 100,
            'is_active' => true,
        ]);

        // Started 3 days ago (total required = 300)
        $threeDaysAgo = Carbon::now()->subDays(2)->format('Y-m-d');
        $progress = UserTasbeehProgress::create([
            'user_id' => $user->id,
            'tasbeeh_id' => $t->id,
            'total_completed' => 0,
            'tracking_start_date' => $threeDaysAgo,
        ]);

        // 1st Click: Advances 1 day's target (+100) -> completed = 100, remaining = 200
        $res1 = $this->postJson(route('admin.zikr.counter.complete-today', $t), ['user_id' => $user->id]);
        $res1->assertOk()->assertJson(['success' => true, 'added_count' => 100]);
        $this->assertSame(100, (int) $progress->fresh()->total_completed);

        // 2nd Click: Advances another day's target (+100) -> completed = 200, remaining = 100
        $res2 = $this->postJson(route('admin.zikr.counter.complete-today', $t), ['user_id' => $user->id]);
        $res2->assertOk()->assertJson(['success' => true, 'added_count' => 100]);
        $this->assertSame(200, (int) $progress->fresh()->total_completed);

        // 3rd Click: Completes the remaining 100 -> completed = 300, remaining = 0
        $res3 = $this->postJson(route('admin.zikr.counter.complete-today', $t), ['user_id' => $user->id]);
        $res3->assertOk()->assertJson(['success' => true, 'added_count' => 100]);
        $this->assertSame(300, (int) $progress->fresh()->total_completed);

        // 4th Click: Already completed -> No count added
        $res4 = $this->postJson(route('admin.zikr.counter.complete-today', $t), ['user_id' => $user->id]);
        $res4->assertOk()->assertJson(['already_completed' => true]);
        $this->assertSame(300, (int) $progress->fresh()->total_completed);
    }

    public function test_read_today_24_hour_log_resets_to_zero_on_new_day(): void
    {
        $user = $this->muslimUser;
        $this->actingAs($user);
        $service = app(ZikrService::class);

        $t = Tasbeeh::create([
            'title' => '24-Hour Daily Log Tasbeeh',
            'arabic_text' => 'سُبْحَانَ اللَّهِ',
            'daily_target' => 100,
            'is_active' => true,
        ]);

        // Day 1: User reads 75 today
        $this->postJson(route('admin.zikr.counter.increment', $t), ['count' => 75])->assertOk();

        $statsDay1 = $service->calculateTasbeehStats($user, $t);
        $this->assertSame(75, $statsDay1['today_completed']);
        $this->assertSame(25, $statsDay1['today_remaining']);

        $summaryDay1 = $service->getDashboardSummary($user);
        $this->assertSame(75, $summaryDay1['overall_today_completed']);

        // Day 2 (Tomorrow): Date advances to tomorrow
        Carbon::setTestNow(Carbon::now($service->getTimezone())->addDay());

        $statsDay2 = $service->calculateTasbeehStats($user, $t);
        // Total completed remains 75 in cycle, but Read Today resets cleanly to 0!
        $this->assertSame(0, $statsDay2['today_completed']);
        $this->assertSame(100, $statsDay2['today_remaining']);

        $summaryDay2 = $service->getDashboardSummary($user);
        $this->assertSame(0, $summaryDay2['overall_today_completed']);

        // User reads 50 on Day 2
        $this->postJson(route('admin.zikr.counter.increment', $t), ['count' => 50])->assertOk();

        $statsDay2Updated = $service->calculateTasbeehStats($user, $t);
        $this->assertSame(50, $statsDay2Updated['today_completed']);
        $this->assertSame(125, $statsDay2Updated['total_completed']); // 75 + 50 = 125 lifetime/cycle

        Carbon::setTestNow(); // Clear mocked time
    }
}

