<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tasbeeh;
use App\Models\User;
use App\Services\ZikrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TasbeehAdminController extends Controller
{
    public function __construct(
        protected ZikrService $zikrService
    ) {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (! ($user->hasAnyRole(['Super Admin', 'Admin', 'admin']) || $user->can('manage tasbeeh'))) {
                abort(403, 'Unauthorized. Admin access required to manage Tasbeeh.');
            }
            return $next($request);
        });
    }

    public function index(Request $request): View|JsonResponse
    {
        $currentUser = $request->user();
        $muslimUsers = User::query()->muslim()->orderBy('name')->get();

        // Selected user resolution (fallback to first Muslim user if admin is non-Muslim)
        $selectedUserId = $request->input('user_id');
        $selectedUser = null;

        if ($selectedUserId && ($currentUser->hasAnyRole(['Super Admin', 'Admin', 'admin']) || $currentUser->can('manage tasbeeh'))) {
            $selectedUser = User::query()->muslim()->find($selectedUserId);
        }

        if (! $selectedUser) {
            $selectedUser = $currentUser->isMuslim() ? $currentUser : $muslimUsers->first();
        }

        $targetUser = $selectedUser ?? $currentUser;

        $tasbeehs = Tasbeeh::query()->ordered()->get();
        $progressMap = $targetUser ? $targetUser->tasbeehProgress()->get()->keyBy('tasbeeh_id') : collect();

        $showPanels = session('zikr_show_panels', false);
        if ($request->filled('from_date') || $request->filled('to_date')) {
            $showPanels = true;
        }

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $summary = $targetUser ? $this->zikrService->getDashboardSummary($targetUser, $fromDate, $toDate) : null;

        if (($request->wantsJson() && ! $request->acceptsHtml()) || $request->query('json') || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'date_range' => $summary['date_range'] ?? null,
                'formatted_total' => isset($summary['date_range']['total_count']) ? number_format($summary['date_range']['total_count']) : '0',
            ]);
        }

        $tasbeehsWithStats = $tasbeehs->map(function ($tasbeeh) use ($targetUser, $progressMap, $summary) {
            $progress = $progressMap->get($tasbeeh->id);
            $stats = $this->zikrService->calculateTasbeehStats($targetUser, $tasbeeh, $progress);
            if (!empty($summary['date_range']['is_filtered'])) {
                $stats['range_completed'] = (int) ($summary['date_range']['tasbeeh_counts'][$tasbeeh->id] ?? 0);
            }
            $tasbeeh->stats = $stats;
            return $tasbeeh;
        });

        return view('admin.zikr.tasbeehs.index', [
            'tasbeehs' => $tasbeehsWithStats,
            'user' => $targetUser,
            'selectedUser' => $targetUser,
            'muslimUsers' => $muslimUsers,
            'summary' => $summary,
            'showPanels' => $showPanels,
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

        $tasbeeh = Tasbeeh::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Tasbeeh '{$tasbeeh->title}' created successfully.",
            'tasbeeh' => $tasbeeh,
        ]);
    }

    public function update(Request $request, Tasbeeh $tasbeeh): JsonResponse
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

        $tasbeeh->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Tasbeeh '{$tasbeeh->title}' updated successfully.",
            'tasbeeh' => $tasbeeh,
        ]);
    }

    public function toggle(Tasbeeh $tasbeeh): JsonResponse
    {
        $tasbeeh->update(['is_active' => ! $tasbeeh->is_active]);

        return response()->json([
            'success' => true,
            'message' => "Tasbeeh status changed to " . ($tasbeeh->is_active ? 'Active' : 'Inactive') . ".",
            'is_active' => $tasbeeh->is_active,
        ]);
    }

    public function destroy(Tasbeeh $tasbeeh): JsonResponse
    {
        $title = $tasbeeh->title;
        $tasbeeh->delete();

        return response()->json([
            'success' => true,
            'message' => "Tasbeeh '{$title}' deleted successfully.",
        ]);
    }
}

