<?php

namespace App\Services;

use App\Models\DateOfBirth;
use App\Models\PwaSetting;
use App\Models\PwaSyncLog;
use App\Models\Tasbeeh;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PwaService
{
    protected ZikrService $zikrService;

    public function __construct(ZikrService $zikrService)
    {
        $this->zikrService = $zikrService;
    }

    public function getSettings(): PwaSetting
    {
        return PwaSetting::getSettings();
    }

    public function isAppActive(): bool
    {
        return (bool) $this->getSettings()->is_active;
    }

    /**
     * Builds standard Web App Manifest array from current database settings.
     */
    public function getManifestData(): array
    {
        $settings = $this->getSettings();

        $icon192 = !empty($settings->icon_192) ? '/' . ltrim($settings->icon_192, '/') : '/assets/pwa-icons/icon-192x192.png';
        $icon512 = !empty($settings->icon_512) ? '/' . ltrim($settings->icon_512, '/') : '/assets/pwa-icons/icon-512x512.png';
        $iconMaskable = !empty($settings->icon_maskable) ? '/' . ltrim($settings->icon_maskable, '/') : $icon512;

        $icons = [
            [
                'src' => $icon192,
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => $icon512,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => $iconMaskable,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
        ];

        return [
            'id' => '/?source=pwa',
            'name' => $settings->app_name ?? 'Amanullah Portfolio & Management',
            'short_name' => $settings->short_name ?? 'Amanullah',
            'description' => $settings->description ?? 'Official Portfolio and Islamic Management System',
            'start_url' => '/admin',
            'scope' => '/',
            'display' => $settings->display_mode ?? 'standalone',
            'orientation' => $settings->orientation ?? 'portrait-primary',
            'theme_color' => $settings->theme_color ?? '#070d18',
            'background_color' => $settings->background_color ?? '#070d18',
            'icons' => $icons,
            'shortcuts' => [
                [
                    'name' => 'Tasbeeh Counter',
                    'short_name' => 'Tasbeeh',
                    'description' => 'Open Islamic Zikr and Tasbeeh Counter',
                    'url' => '/admin/zikr',
                    'icons' => [
                        [
                            'src' => $icon192,
                            'sizes' => '192x192',
                            'type' => 'image/png',
                        ],
                    ],
                ],
                [
                    'name' => 'Dashboard',
                    'short_name' => 'Dashboard',
                    'description' => 'Admin Overview Dashboard',
                    'url' => '/admin',
                    'icons' => [
                        [
                            'src' => $icon192,
                            'sizes' => '192x192',
                            'type' => 'image/png',
                        ],
                    ],
                ],
            ],
            'categories' => ['lifestyle', 'utilities', 'productivity'],
            'lang' => 'en-US',
            'dir' => 'auto',
        ];
    }

    /**
     * Process batch of offline queue operations from client with idempotency and transaction safety.
     */
    public function processPushSync(User $user, array $operations): array
    {
        $settings = $this->getSettings();
        if (!$settings->is_active) {
            return [
                'success' => false,
                'app_active' => false,
                'message' => $settings->disabled_message ?? 'Application is currently disabled.',
                'synced_operations' => [],
            ];
        }

        $syncedResults = [];
        $idMappings = []; // client_temp_id => server_id

        foreach ($operations as $op) {
            $opUuid = $op['uuid'] ?? (string) Str::uuid();
            $idempotencyKey = $op['idempotency_key'] ?? $opUuid;
            $entity = $op['entity'] ?? 'unknown';
            $action = $op['action'] ?? 'create';
            $clientTempId = $op['temp_id'] ?? null;
            $payload = $op['payload'] ?? [];

            // 1. Check if already processed (Idempotency protection)
            $existingLog = PwaSyncLog::where('user_id', $user->id)
                ->where('operation_uuid', $opUuid)
                ->first();

            if ($existingLog) {
                $syncedResults[] = [
                    'uuid' => $opUuid,
                    'temp_id' => $clientTempId,
                    'server_id' => $existingLog->server_id,
                    'status' => $existingLog->status,
                    'message' => 'Already processed (idempotent)',
                ];

                if ($clientTempId && $existingLog->server_id) {
                    $idMappings[$clientTempId] = $existingLog->server_id;
                }
                continue;
            }

            // 2. Execute operation inside Database Transaction
            try {
                $serverId = null;
                $status = 'synced';
                $errorMessage = null;

                DB::transaction(function () use ($user, $entity, $action, $payload, &$serverId, &$status, &$errorMessage) {
                    switch ($entity) {
                        case 'zikr_count':
                        case 'tasbeeh_count':
                            $tasbeehId = $payload['tasbeeh_id'] ?? null;
                            $count = (int) ($payload['count'] ?? 1);
                            $tasbeeh = Tasbeeh::find($tasbeehId);
                            if ($tasbeeh) {
                                $this->zikrService->addCount($user, $tasbeeh, $count);
                                $serverId = $tasbeeh->id;
                            } else {
                                $status = 'failed';
                                $errorMessage = "Tasbeeh #{$tasbeehId} not found.";
                            }
                            break;

                        case 'tasbeeh_complete_today':
                            $tasbeehId = $payload['tasbeeh_id'] ?? null;
                            $tasbeeh = Tasbeeh::find($tasbeehId);
                            if ($tasbeeh) {
                                $this->zikrService->completeSingleForToday($user, $tasbeeh);
                                $serverId = $tasbeeh->id;
                            } else {
                                $status = 'failed';
                                $errorMessage = "Tasbeeh #{$tasbeehId} not found.";
                            }
                            break;

                        case 'zikr_complete_all':
                            $this->zikrService->completeAllForToday($user);
                            $serverId = 1;
                            break;

                        case 'zikr_reset_all':
                            $this->zikrService->resetAllProgress($user);
                            $serverId = 1;
                            break;

                        case 'tasbeeh_reset_single':
                            $tasbeehId = $payload['tasbeeh_id'] ?? null;
                            $tasbeeh = Tasbeeh::find($tasbeehId);
                            if ($tasbeeh) {
                                $this->zikrService->resetProgress($user, $tasbeeh);
                                $serverId = $tasbeeh->id;
                            } else {
                                $status = 'failed';
                                $errorMessage = "Tasbeeh #{$tasbeehId} not found.";
                            }
                            break;

                        case 'lifetime_reset':
                            $this->zikrService->resetLifetimeZikr($user);
                            $serverId = 1;
                            break;

                        case 'namaz_attendance_status':
                            $targetUserId = (int) ($payload['user_id'] ?? $user->id);
                            // IDOR Protection: non-admins can only sync for themselves
                            if ($targetUserId !== $user->id && !($user->can('view namaz attendance') || $user->hasAnyRole(['Super Admin', 'Admin', 'admin']))) {
                                $targetUserId = $user->id;
                            }
                            $targetUser = User::find($targetUserId) ?? $user;
                            $date = $payload['attendance_date'] ?? null;
                            $prayer = $payload['prayer'] ?? null;
                            $prayerStatus = $payload['status'] ?? null;

                            if ($date && $prayer) {
                                $namazService = app(\App\Services\NamazAttendanceService::class);
                                $namazService->updatePrayerStatus($targetUser, $date, $prayer, $prayerStatus ?: null);
                                $serverId = $targetUser->id;
                            } else {
                                $status = 'failed';
                                $errorMessage = 'Missing date or prayer for namaz attendance.';
                            }
                            break;

                        case 'namaz_attendance_day':
                            $targetUserId = (int) ($payload['user_id'] ?? $user->id);
                            // IDOR Protection: non-admins can only sync for themselves
                            if ($targetUserId !== $user->id && !($user->can('view namaz attendance') || $user->hasAnyRole(['Super Admin', 'Admin', 'admin']))) {
                                $targetUserId = $user->id;
                            }
                            $targetUser = User::find($targetUserId) ?? $user;
                            $date = $payload['attendance_date'] ?? null;
                            $statuses = [
                                'fajr' => $payload['fajr_status'] ?? null,
                                'zuhr' => $payload['zuhr_status'] ?? null,
                                'asr' => $payload['asr_status'] ?? null,
                                'maghrib' => $payload['maghrib_status'] ?? null,
                                'isha' => $payload['isha_status'] ?? null,
                            ];

                            if ($date) {
                                $namazService = app(\App\Services\NamazAttendanceService::class);
                                $namazService->updateDayAttendance($targetUser, $date, $statuses);
                                $serverId = $targetUser->id;
                            } else {
                                $status = 'failed';
                                $errorMessage = 'Missing date for namaz day attendance.';
                            }
                            break;

                        case 'date_of_birth':
                            $recordId = $payload['id'] ?? null;
                            $name = $payload['name'] ?? null;
                            $fatherName = $payload['father_name'] ?? null;
                            $startDate = $payload['start_date'] ?? null;
                            $endDate = $payload['end_date'] ?? null;

                            if ($action === 'create') {
                                if (!$user->can('create date of birth') && !$user->hasAnyRole(['Super Admin', 'Admin', 'admin'])) {
                                    $status = 'failed';
                                    $errorMessage = 'Unauthorized to create date of birth records.';
                                    break;
                                }
                                if ($name && $startDate) {
                                    $dob = DateOfBirth::create([
                                        'name' => $name,
                                        'father_name' => $fatherName,
                                        'start_date' => $startDate,
                                        'end_date' => $endDate,
                                    ]);
                                    $serverId = $dob->id;
                                } else {
                                    $status = 'failed';
                                    $errorMessage = 'Missing name or birth date.';
                                }
                            } elseif ($action === 'update') {
                                if (!$user->can('update date of birth') && !$user->hasAnyRole(['Super Admin', 'Admin', 'admin'])) {
                                    $status = 'failed';
                                    $errorMessage = 'Unauthorized to update date of birth records.';
                                    break;
                                }
                                $dob = DateOfBirth::find($recordId);
                                if ($dob) {
                                    $dob->update([
                                        'name' => $name ?? $dob->name,
                                        'father_name' => $fatherName ?? $dob->father_name,
                                        'start_date' => $startDate ?? $dob->start_date,
                                        'end_date' => $endDate ?? $dob->end_date,
                                    ]);
                                    $serverId = $dob->id;
                                } else {
                                    $status = 'failed';
                                    $errorMessage = "DOB record #{$recordId} not found.";
                                }
                            } elseif ($action === 'delete') {
                                if (!$user->can('delete date of birth') && !$user->hasAnyRole(['Super Admin', 'Admin', 'admin'])) {
                                    $status = 'failed';
                                    $errorMessage = 'Unauthorized to delete date of birth records.';
                                    break;
                                }
                                $dob = DateOfBirth::find($recordId);
                                if ($dob) {
                                    $dob->delete();
                                    $serverId = $recordId;
                                } else {
                                    $status = 'synced'; // already deleted
                                    $serverId = $recordId;
                                }
                            }
                            break;

                        default:
                            $status = 'synced';
                            $serverId = 1;
                            break;
                    }
                });

                // Record audit log
                PwaSyncLog::create([
                    'user_id' => $user->id,
                    'operation_uuid' => $opUuid,
                    'idempotency_key' => $idempotencyKey,
                    'entity' => $entity,
                    'action' => $action,
                    'status' => $status,
                    'payload' => $payload,
                    'server_id' => $serverId,
                    'client_temp_id' => $clientTempId,
                    'error_message' => $errorMessage,
                    'retry_count' => (int) ($op['retry_count'] ?? 0),
                ]);

                if ($clientTempId && $serverId) {
                    $idMappings[$clientTempId] = $serverId;
                }

                $syncedResults[] = [
                    'uuid' => $opUuid,
                    'temp_id' => $clientTempId,
                    'server_id' => $serverId,
                    'status' => $status,
                    'error' => $errorMessage,
                ];
            } catch (\Throwable $e) {
                Log::error("PWA Sync Error on operation {$opUuid}: " . $e->getMessage());

                PwaSyncLog::create([
                    'user_id' => $user->id,
                    'operation_uuid' => $opUuid,
                    'idempotency_key' => $idempotencyKey,
                    'entity' => $entity,
                    'action' => $action,
                    'status' => 'failed',
                    'payload' => $payload,
                    'client_temp_id' => $clientTempId,
                    'error_message' => $e->getMessage(),
                    'retry_count' => (int) ($op['retry_count'] ?? 0) + 1,
                ]);

                $syncedResults[] = [
                    'uuid' => $opUuid,
                    'temp_id' => $clientTempId,
                    'server_id' => null,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => true,
            'app_active' => true,
            'message' => 'Sync operations processed successfully.',
            'synced_operations' => $syncedResults,
            'id_mappings' => $idMappings,
            'server_time' => now()->toIso8601String(),
        ];
    }

    /**
     * Pull latest delta records for local IndexedDB caching.
     */
    public function processPullSync(User $user, ?string $lastSyncedAt = null): array
    {
        $settings = $this->getSettings();
        if (!$settings->is_active) {
            return [
                'success' => false,
                'app_active' => false,
                'message' => $settings->disabled_message ?? 'Application is currently disabled.',
            ];
        }

        // 1. Zikr summary and tasbeehs
        $zikrSummary = $this->zikrService->getDashboardSummary($user);

        // 2. Active tasbeeh list
        $tasbeehs = Tasbeeh::query()
            ->active()
            ->ordered()
            ->get([
                'id',
                'title',
                'arabic_text',
                'urdu_meaning',
                'daily_target',
                'sort_order',
                'is_active',
                'updated_at',
            ]);

        return [
            'success' => true,
            'app_active' => true,
            'server_time' => now()->toIso8601String(),
            'app_version' => $settings->app_version,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'is_muslim' => $user->isMuslim(),
            ],
            'data' => [
                'tasbeehs' => $tasbeehs,
                'zikr_summary' => $zikrSummary,
            ],
        ];
    }
}

