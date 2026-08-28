<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NamazAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NamazDashboardController extends Controller
{
    public function __construct(protected NamazAttendanceService $namazService)
    {
        $this->middleware('permission:view namaz dashboard|namaz_dashboard.view');
    }

    public function index(Request $request): View
    {
        $muslimUsers = User::query()->muslim()->orderBy('name')->get();

        $selectedUserId = $request->integer('user_id');
        $selectedUser = $selectedUserId ? $muslimUsers->firstWhere('id', $selectedUserId) : null;

        $tz = $this->namazService->getTimezone();
        $now = $this->namazService->now();

        $filter = $request->string('filter', 'month')->toString();
        $startDateInput = $request->string('start_date')->toString();
        $endDateInput = $request->string('end_date')->toString();

        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput, $tz)->startOfDay();
            $endDate = Carbon::parse($endDateInput, $tz)->startOfDay();
        } else {
            if ($filter === 'today') {
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
            } elseif ($filter === 'week') {
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
            } elseif ($filter === 'all') {
                $earliest = $selectedUser?->namaz_start_date ?: $muslimUsers->whereNotNull('namaz_start_date')->min('namaz_start_date');
                $startDate = $earliest ? Carbon::parse($earliest, $tz)->startOfDay() : $now->copy()->subMonths(3)->startOfDay();
                $endDate = $now->copy()->endOfDay();
            } else { // 'month'
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
            }
        }

        $stats = $this->namazService->calculateDashboardStatistics($selectedUser, $startDate, $endDate);

        return view('admin.namaz.dashboard.index', [
            'muslimUsers' => $muslimUsers,
            'selectedUser' => $selectedUser,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'filter' => $filter,
            'stats' => $stats,
            'prayers' => NamazAttendanceService::PRAYERS,
        ]);
    }
}

