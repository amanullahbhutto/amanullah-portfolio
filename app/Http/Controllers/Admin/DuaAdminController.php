<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dua;
use App\Models\User;
use App\Services\DuaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DuaAdminController extends Controller
{
    public function __construct(
        protected DuaService $duaService
    ) {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (! ($user->isMuslim() || $user->hasAnyRole(['Super Admin', 'Admin', 'admin']) || $user->can('manage tasbeeh'))) {
                abort(403, 'Unauthorized. Access restricted to Muslim users and Administrators.');
            }
            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $muslimUsers = User::query()->muslim()->orderBy('name')->get();

        $selectedUserId = $request->input('user_id');
        $selectedUser = null;

        if ($selectedUserId && ($currentUser->hasAnyRole(['Super Admin', 'Admin', 'admin']) || $currentUser->can('manage tasbeeh'))) {
            $selectedUser = User::query()->muslim()->find($selectedUserId);
        }

        if (! $selectedUser) {
            $selectedUser = $currentUser->isMuslim() ? $currentUser : $muslimUsers->first();
        }

        $targetUser = $selectedUser ?? $currentUser;
        $summary = $targetUser ? $this->duaService->getDashboardSummary($targetUser) : null;

        return view('admin.duas.index', [
            'duas' => $summary['duas'] ?? [],
            'user' => $targetUser,
            'selectedUser' => $targetUser,
            'muslimUsers' => $muslimUsers,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'arabic_text' => ['required', 'string'],
            'urdu_meaning' => ['nullable', 'string'],
            'daily_target' => ['required', 'integer', 'min:0', 'max:100000'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'transliteration' => ['nullable', 'string'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['created_by'] = $request->user()->id;

        $dua = Dua::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Dua '{$dua->title}' created successfully.",
            'dua' => $dua,
        ]);
    }

    public function update(Request $request, Dua $dua): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'arabic_text' => ['required', 'string'],
            'urdu_meaning' => ['nullable', 'string'],
            'daily_target' => ['required', 'integer', 'min:0', 'max:100000'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'transliteration' => ['nullable', 'string'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $dua->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Dua '{$dua->title}' updated successfully.",
            'dua' => $dua,
        ]);
    }

    public function toggle(Dua $dua): JsonResponse
    {
        $dua->update(['is_active' => ! $dua->is_active]);

        return response()->json([
            'success' => true,
            'message' => "Dua status changed to " . ($dua->is_active ? 'Active' : 'Inactive') . ".",
            'is_active' => $dua->is_active,
        ]);
    }

    public function destroy(Dua $dua): JsonResponse
    {
        $title = $dua->title;
        $dua->delete();

        return response()->json([
            'success' => true,
            'message' => "Dua '{$title}' deleted successfully.",
        ]);
    }

    public function increment(Request $request, Dua $dua): JsonResponse
    {
        $user = $this->resolveTargetUser($request);
        $delta = max(1, (int) $request->input('delta', 1));
        $stats = $this->duaService->increment($user, $dua, $delta);

        return response()->json([
            'success' => true,
            'message' => "+{$delta} added for '{$dua->title}'.",
            'stats' => $stats,
        ]);
    }

    public function manual(Request $request, Dua $dua): JsonResponse
    {
        $user = $this->resolveTargetUser($request);
        $count = max(1, (int) $request->input('count', 1));
        $stats = $this->duaService->manual($user, $dua, $count);

        return response()->json([
            'success' => true,
            'message' => "+{$count} count added for '{$dua->title}'.",
            'stats' => $stats,
        ]);
    }

    public function completeToday(Request $request, Dua $dua): JsonResponse
    {
        $user = $this->resolveTargetUser($request);
        $stats = $this->duaService->completeToday($user, $dua);

        return response()->json([
            'success' => true,
            'message' => "Today's target completed for '{$dua->title}'.",
            'stats' => $stats,
        ]);
    }

    public function completeAllToday(Request $request): JsonResponse
    {
        $user = $this->resolveTargetUser($request);
        $summary = $this->duaService->completeAllToday($user);

        return response()->json([
            'success' => true,
            'message' => "All active Duas marked completed for today.",
            'summary' => $summary,
        ]);
    }

    public function reset(Request $request, Dua $dua): JsonResponse
    {
        $user = $this->resolveTargetUser($request);
        $stats = $this->duaService->reset($user, $dua);

        return response()->json([
            'success' => true,
            'message' => "Dua '{$dua->title}' reset to 0 with start date today.",
            'stats' => $stats,
        ]);
    }

    public function resetAll(Request $request): JsonResponse
    {
        $user = $this->resolveTargetUser($request);
        $summary = $this->duaService->resetAll($user);

        return response()->json([
            'success' => true,
            'message' => "All Duas reset to 0 with tracking start date today.",
            'summary' => $summary,
        ]);
    }

    public function updateStartDate(Request $request, Dua $dua): JsonResponse
    {
        $request->validate(['start_date' => ['required', 'date']]);
        $user = $this->resolveTargetUser($request);
        $stats = $this->duaService->updateStartDate($user, $dua, $request->input('start_date'));

        return response()->json([
            'success' => true,
            'message' => "Start date updated successfully.",
            'stats' => $stats,
        ]);
    }

    protected function resolveTargetUser(Request $request): User
    {
        $currentUser = $request->user();
        $targetUserId = $request->input('user_id');

        if ($targetUserId && ($currentUser->hasAnyRole(['Super Admin', 'Admin', 'admin']) || $currentUser->can('manage tasbeeh'))) {
            $user = User::query()->muslim()->find($targetUserId);
            if ($user) {
                return $user;
            }
        }

        return $currentUser;
    }
}

