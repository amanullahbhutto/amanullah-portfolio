<?php

namespace Tests\Feature;

use App\Models\DateOfBirth;
use App\Models\NamazAttendance;
use App\Models\PwaSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Muslim', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
    }

    public function test_security_headers_are_attached_to_all_web_responses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertTrue($response->headers->has('Permissions-Policy'));
    }

    public function test_pwa_push_sync_prevents_idor_attendance_tampering(): void
    {
        PwaSetting::create([
            'is_active' => true,
            'app_name' => 'Amanullah Portfolio',
            'short_name' => 'Portfolio',
        ]);

        $victimUser = User::factory()->create([
            'namaz_start_date' => '2026-01-01',
        ]);
        $victimUser->assignRole('Muslim');

        $attackerUser = User::factory()->create([
            'namaz_start_date' => '2026-01-01',
        ]);
        $attackerUser->assignRole(['Muslim', 'User']);

        $payload = [
            'operations' => [
                [
                    'uuid' => 'sec-test-uuid-1',
                    'entity' => 'namaz_attendance_status',
                    'action' => 'update',
                    'payload' => [
                        'user_id' => $victimUser->id, // Attacker tries to modify victim's attendance
                        'attendance_date' => '2026-08-30',
                        'prayer' => 'fajr',
                        'status' => 'jamat',
                    ],
                ],
            ],
        ];

        // Send push sync as attacker
        $response = $this->actingAs($attackerUser)->postJson('/pwa/sync/push', $payload);

        $response->assertStatus(200);

        // Verify that victim's attendance was NOT touched
        $this->assertDatabaseMissing('namaz_attendances', [
            'user_id' => $victimUser->id,
            'attendance_date' => '2026-08-30',
        ]);

        // Verify that it was redirected to attacker's own attendance record
        $this->assertDatabaseHas('namaz_attendances', [
            'user_id' => $attackerUser->id,
            'fajr_status' => 'jamat',
        ]);
    }

    public function test_pwa_sync_unauthenticated_request_is_rejected(): void
    {
        $response = $this->postJson('/pwa/sync/push', [
            'operations' => [],
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'authenticated' => false,
        ]);
    }
}
