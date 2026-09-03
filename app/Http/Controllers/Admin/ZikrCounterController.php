<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tasbeeh;
use App\Models\User;
use App\Services\ZikrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ZikrCounterController extends Controller
{
    public function __construct(
        protected ZikrService $zikrService
    ) {}

    protected function authorizeAccess(Request $request, ?User $targetUser = null): User
    {
        $currentUser = $request->user();

        if (! ($currentUser->isMuslim() || $currentUser->can('view zikr') || $currentUser->hasAnyRole(['Super Admin', 'Admin', 'admin']))) {
            abort(403, 'Unauthorized. Zikr module is accessible to Muslim users only.');
        }

        if ($targetUser && $targetUser->id !== $currentUser->id) {
            if (! ($currentUser->hasAnyRole(['Super Admin', 'Admin', 'admin']) || $currentUser->can('manage tasbeeh'))) {
                abort(403, 'Unauthorized to view or modify another user\'s zikr records.');
            }
            return $targetUser;
        }

        return $currentUser;
    }

    public function show(Request $request, Tasbeeh $tasbeeh): View
    {
        $targetUser = null;
        if ($request->filled('user_id')) {
            $targetUser = User::find($request->input('user_id'));
        }

        $user = $this->authorizeAccess($request, $targetUser);
        $progress = $this->zikrService->getOrCreateProgress($user, $tasbeeh);
        $stats = $this->zikrService->calculateTasbeehStats($user, $tasbeeh, $progress);

        return view('admin.zikr.counter.show', compact('tasbeeh', 'user', 'progress', 'stats'));
    }

    public function increment(Request $request, Tasbeeh $tasbeeh): JsonResponse
    {
        $targetUser = null;
        if ($request->filled('user_id')) {
            $targetUser = User::find($request->input('user_id'));
        }

        $user = $this->authorizeAccess($request, $targetUser);

        $validated = $request->validate([
            'count' => ['nullable', 'integer', 'min:1', 'max:2000000000'],
        ]);

        $count = (int) ($validated['count'] ?? 1);
        $result = $this->zikrService->addCount($user, $tasbeeh, $count, 'live');

        return response()->json($result);
    }

    public function manual(Request $request, Tasbeeh $tasbeeh): JsonResponse
    {
        $targetUser = null;
        if ($request->filled('user_id')) {
            $targetUser = User::find($request->input('user_id'));
        }

        $user = $this->authorizeAccess($request, $targetUser);

        $validated = $request->validate([
            'count' => ['required', 'integer', 'not_in:0', 'min:-2000000000', 'max:2000000000'],
        ]);

        $result = $this->zikrService->addCount($user, $tasbeeh, (int) $validated['count'], 'manual');

        return response()->json($result);
    }

    public function reset(Request $request, Tasbeeh $tasbeeh): JsonResponse
    {
        $targetUser = null;
        if ($request->filled('user_id')) {
            $targetUser = User::find($request->input('user_id'));
        }

        $user = $this->authorizeAccess($request, $targetUser);
        $result = $this->zikrService->resetProgress($user, $tasbeeh);

        return response()->json($result);
    }

    public function updateStartDate(Request $request, Tasbeeh $tasbeeh): JsonResponse
    {
        $targetUser = null;
        if ($request->filled('user_id')) {
            $targetUser = User::find($request->input('user_id'));
        }

        $user = $this->authorizeAccess($request, $targetUser);

        $validated = $request->validate([
            'tracking_start_date' => ['required', 'date'],
        ]);

        $result = $this->zikrService->updateStartDate($user, $tasbeeh, $validated['tracking_start_date']);

        return response()->json($result);
    }

    public function completeToday(Request $request, Tasbeeh $tasbeeh): JsonResponse
    {
        $targetUser = null;
        if ($request->filled('user_id')) {
            $targetUser = User::find($request->input('user_id'));
        }

        $user = $this->authorizeAccess($request, $targetUser);
        $result = $this->zikrService->completeSingleForToday($user, $tasbeeh);

        return response()->json($result);
    }

    public function completeAllToday(Request $request): JsonResponse
    {
        $targetUser = null;
        if ($request->filled('user_id')) {
            $targetUser = User::find($request->input('user_id'));
        }

        $user = $this->authorizeAccess($request, $targetUser);
        $result = $this->zikrService->completeAllForToday($user);

        return response()->json($result);
    }

    public function resetAll(Request $request): JsonResponse
    {
        $targetUser = null;
        if ($request->filled('user_id')) {
            $targetUser = User::find($request->input('user_id'));
        }

        $user = $this->authorizeAccess($request, $targetUser);
        $result = $this->zikrService->resetAllProgress($user);

        return response()->json($result);
    }

    public function resetLifetime(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $authenticatedUser = $request->user();
        if (! Hash::check($request->input('password'), $authenticatedUser->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password! Please enter your correct login password to reset Lifetime Total.',
            ], 422);
        }

        $targetUser = null;
        if ($request->filled('user_id')) {
            $targetUser = User::find($request->input('user_id'));
        }

        $user = $this->authorizeAccess($request, $targetUser);
        $result = $this->zikrService->resetLifetimeZikr($user);

        return response()->json($result);
    }
}

