<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ZikrService;
use Illuminate\Http\Request;
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

        return view('admin.zikr.dashboard.index', compact('summary', 'selectedUser', 'muslimUsers'));
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

