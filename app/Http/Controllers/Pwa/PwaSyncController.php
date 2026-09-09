<?php

namespace App\Http\Controllers\Pwa;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PwaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PwaSyncController extends Controller
{
    protected PwaService $pwaService;

    public function __construct(PwaService $pwaService)
    {
        $this->pwaService = $pwaService;
    }

    /**
     * Check current application status, version, and user session.
     */
    public function status(Request $request): JsonResponse
    {
        $settings = $this->pwaService->getSettings();
        $user = $request->user();

        return response()->json([
            'success' => true,
            'is_active' => (bool) $settings->is_active,
            'app_version' => $settings->app_version,
            'app_name' => $settings->app_name,
            'short_name' => $settings->short_name,
            'offline_mode_enabled' => (bool) $settings->offline_mode_enabled,
            'auto_sync_enabled' => (bool) $settings->auto_sync_enabled,
            'max_offline_days' => (int) $settings->max_offline_days,
            'messages' => [
                'offline' => $settings->offline_message,
                'disabled' => $settings->disabled_message,
                'maintenance' => $settings->maintenance_message,
            ],
            'server_time' => now()->toIso8601String(),
            'authenticated' => (bool) $user,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->hasAnyRole(['Super Admin', 'Admin', 'admin']),
            ] : null,
        ]);
    }

    protected function resolveTargetUser(Request $request): User
    {
        $currentUser = $request->user();
        $targetUserId = $request->query('user_id') ?? $request->input('user_id');

        if ($targetUserId && ($currentUser->hasAnyRole(['Super Admin', 'Admin', 'admin']) || $currentUser->can('manage tasbeeh') || $currentUser->can('view zikr'))) {
            $found = User::find($targetUserId);
            if ($found) {
                return $found;
            }
        }

        if (!$currentUser->isMuslim()) {
            $firstMuslim = User::query()->muslim()->first();
            if ($firstMuslim) {
                return $firstMuslim;
            }
        }

        return $currentUser;
    }

    /**
     * Push batch of offline operations to server.
     */
    public function push(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'authenticated' => false,
                'message' => 'User session expired. Please log in again to sync pending changes.',
            ], 401);
        }

        $validated = $request->validate([
            'operations' => 'required|array',
            'operations.*.uuid' => 'required|string|max:64',
            'operations.*.entity' => 'required|string|max:64',
            'operations.*.action' => 'required|string|in:create,update,delete',
            'operations.*.temp_id' => 'nullable|string|max:64',
            'operations.*.payload' => 'nullable|array',
            'operations.*.retry_count' => 'nullable|integer',
        ]);

        $targetUser = $this->resolveTargetUser($request);
        $result = $this->pwaService->processPushSync($targetUser, $validated['operations']);

        return response()->json($result);
    }

    /**
     * Pull latest delta updates for offline storage.
     */
    public function pull(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'authenticated' => false,
                'message' => 'User session expired.',
            ], 401);
        }

        $targetUser = $this->resolveTargetUser($request);
        $lastSyncedAt = $request->query('last_synced_at');
        $result = $this->pwaService->processPullSync($targetUser, $lastSyncedAt);

        return response()->json($result);
    }
}

