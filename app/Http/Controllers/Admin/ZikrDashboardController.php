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
}

