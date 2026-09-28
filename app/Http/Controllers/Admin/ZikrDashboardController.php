<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ZikrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ZikrDashboardController extends Controller
{
    public function __construct(
        protected ZikrService $zikrService
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();

        // Server-side Authorization: Must be Muslim or have zikr permissions
        if (! ($currentUser->isMuslim() || $currentUser->can('view zikr') || $currentUser->hasAnyRole(['Super Admin', 'Admin', 'admin']))) {
            abort(403, 'Unauthorized. Zikr module is accessible to Muslim users only.');
        }

        $muslimUsers = User::query()->muslim()->orderBy('name')->get();

        // Selected user resolution
        $selectedUserId = $request->input('user_id');
        $selectedUser = null;

        if ($selectedUserId && ($currentUser->hasAnyRole(['Super Admin', 'Admin', 'admin']) || $currentUser->can('manage tasbeeh'))) {
            $selectedUser = User::query()->muslim()->find($selectedUserId);
        }

        if (! $selectedUser) {
            $selectedUser = $currentUser->isMuslim() ? $currentUser : $muslimUsers->first();
        }

        $summary = $selectedUser ? $this->zikrService->getDashboardSummary($selectedUser) : null;

        // Session-based 5-minute password unlock check for Lifetime, Total Required, Total Complete
        $statsUnlockedAt = session('zikr_stats_unlocked_at');
        $isStatsUnlocked = false;
        $statsUnlockRemaining = 0;

        if ($statsUnlockedAt && (now()->timestamp - (int) $statsUnlockedAt) < 300) {
            $isStatsUnlocked = true;
            $statsUnlockRemaining = max(0, 300 - (now()->timestamp - (int) $statsUnlockedAt));
        } else {
            session()->forget('zikr_stats_unlocked_at');
        }

        return view('admin.zikr.dashboard.index', compact(
            'summary',
            'selectedUser',
            'muslimUsers',
            'isStatsUnlocked',
            'statsUnlockRemaining'
        ));
    }

    public function verifyStatsPassword(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
            'user_id' => ['nullable', 'integer'],
        ]);

        $authenticatedUser = $request->user();
        if (! Hash::check($request->input('password'), $authenticatedUser->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password! Please enter your correct login password.',
            ], 422);
        }

        // 5 minutes unlock session (300 seconds)
        $unlockedAt = now()->timestamp;
        session(['zikr_stats_unlocked_at' => $unlockedAt]);

        $targetUser = null;
        if ($request->filled('user_id') && ($authenticatedUser->hasAnyRole(['Super Admin', 'Admin', 'admin']) || $authenticatedUser->can('manage tasbeeh'))) {
            $targetUser = User::query()->muslim()->find($request->input('user_id'));
        }
        if (! $targetUser) {
            $targetUser = $authenticatedUser->isMuslim() ? $authenticatedUser : User::query()->muslim()->first();
        }

        $summary = $targetUser ? $this->zikrService->getDashboardSummary($targetUser) : null;

        $tasbeehsData = [];
        if ($summary && isset($summary['tasbeehs'])) {
            foreach ($summary['tasbeehs'] as $t) {
                $tasbeehsData[$t['tasbeeh_id']] = [
                    'total_completed' => number_format($t['total_completed']),
                    'total_required' => number_format($t['total_required']),
                    'raw_total_completed' => (int) $t['total_completed'],
                    'raw_total_required' => (int) $t['total_required'],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Password verified. Protected stats unlocked for 5 minutes.',
            'expires_in_seconds' => 300,
            'stats' => [
                'lifetime_total' => $summary ? number_format($summary['lifetime_total']) : '0',
                'raw_lifetime_total' => $summary ? (int) $summary['lifetime_total'] : 0,
                'lifetime_duration' => $summary['lifetime_duration']['formatted_full'] ?? 'Day 1',
                'overall_total_required' => $summary ? number_format($summary['overall_total_required']) : '0',
                'raw_overall_total_required' => $summary ? (int) $summary['overall_total_required'] : 0,
                'overall_total_completed' => $summary ? number_format($summary['overall_total_completed']) : '0',
                'raw_overall_total_completed' => $summary ? (int) $summary['overall_total_completed'] : 0,
                'overall_percentage' => $summary ? $summary['overall_percentage'] : 0,
                'tasbeehs' => $tasbeehsData,
            ],
        ]);
    }

    public function lockStats(Request $request): JsonResponse
    {
        session()->forget('zikr_stats_unlocked_at');

        return response()->json([
            'success' => true,
            'message' => 'Protected stats locked successfully.',
        ]);
    }

    public function updateSettings(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'zikr_arabic_size' => ['nullable', 'integer', 'min:12', 'max:60'],
            'zikr_urdu_size' => ['nullable', 'integer', 'min:10', 'max:40'],
            'zikr_show_arabic' => ['nullable', 'boolean'],
            'zikr_show_urdu' => ['nullable', 'boolean'],
        ]);

        $user->update([
            'zikr_arabic_size' => $request->has('zikr_arabic_size') ? (int) $request->input('zikr_arabic_size') : ($user->zikr_arabic_size ?? 24),
            'zikr_urdu_size' => $request->has('zikr_urdu_size') ? (int) $request->input('zikr_urdu_size') : ($user->zikr_urdu_size ?? 16),
            'zikr_show_arabic' => $request->boolean('zikr_show_arabic', true),
            'zikr_show_urdu' => $request->boolean('zikr_show_urdu', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Zikr display settings saved.',
            'settings' => [
                'arabic_size' => (int) $user->zikr_arabic_size,
                'urdu_size' => (int) $user->zikr_urdu_size,
                'show_arabic' => (bool) $user->zikr_show_arabic,
                'show_urdu' => (bool) $user->zikr_show_urdu,
            ],
        ]);
    }
}

