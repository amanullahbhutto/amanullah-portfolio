<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NamazAttendance;
use App\Models\User;
use App\Services\NamazAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NamazAttendanceController extends Controller
{
    public function __construct(protected NamazAttendanceService $namazService)
    {
        $this->middleware('permission:view namaz attendance|namaz_attendance.view', ['only' => ['index']]);
        $this->middleware('permission:create namaz attendance|namaz_attendance.create|update namaz attendance|namaz_attendance.update', ['only' => ['updateStatus', 'updateStartDate', 'updateDay']]);
        $this->middleware('permission:delete namaz attendance|namaz_attendance.delete', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $muslimUsers = User::query()->muslim()->orderBy('name')->get();

        $selectedUserId = $request->integer('user_id') ?: ($muslimUsers->first()?->id ?? null);
        $selectedUser = $selectedUserId ? $muslimUsers->firstWhere('id', $selectedUserId) : null;

        $tz = $this->namazService->getTimezone();
        $now = $this->namazService->now();

        $startDateInput = $request->string('start_date')->toString();
        $endDateInput = $request->string('end_date')->toString();

        // Default to current month or last 30 days
        $startDate = $startDateInput ? Carbon::parse($startDateInput, $tz)->startOfDay() : $now->copy()->startOfMonth();
        $endDate = $endDateInput ? Carbon::parse($endDateInput, $tz)->startOfDay() : $now->copy()->endOfDay();

        $ledger = collect();
        if ($selectedUser && $selectedUser->namaz_start_date) {
            $ledger = $this->namazService->generateAttendanceLedger($selectedUser, $startDate, $endDate);
        }

        $quickFilter = $request->string('filter', 'month')->toString();

        return view('admin.namaz.attendance.index', [
            'muslimUsers' => $muslimUsers,
            'selectedUser' => $selectedUser,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'quickFilter' => $quickFilter,
            'ledger' => $ledger,
            'prayers' => NamazAttendanceService::PRAYERS,
            'statuses' => [
                NamazAttendanceService::STATUS_JAMAT => 'Jamat',
                NamazAttendanceService::STATUS_WITHOUT_JAMAT => 'Without Jamat',
                NamazAttendanceService::STATUS_KAZA => 'Kaza',
                NamazAttendanceService::STATUS_ABSENT => 'Absent',
            ],
            'namazService' => $this->namazService,
        ]);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'attendance_date' => ['required', 'date_format:Y-m-d'],
            'prayer' => ['required', 'string', Rule::in(NamazAttendanceService::PRAYERS)],
            'status' => ['nullable', 'string', Rule::in([
                NamazAttendanceService::STATUS_JAMAT,
                NamazAttendanceService::STATUS_WITHOUT_JAMAT,
                NamazAttendanceService::STATUS_KAZA,
                NamazAttendanceService::STATUS_ABSENT,
            ])],
        ]);

        $user = User::query()->muslim()->findOrFail($validated['user_id']);

        if (! $user->namaz_start_date) {
            return response()->json([
                'success' => false,
                'message' => "Namaz attendance has not been started for {$user->name}. Please set Namaz Start Date first.",
            ], 422);
        }

        $tz = $this->namazService->getTimezone();
        $attendanceDate = Carbon::parse($validated['attendance_date'], $tz)->startOfDay();
        $userStart = Carbon::parse($user->namaz_start_date, $tz)->startOfDay();

        if ($attendanceDate->lt($userStart)) {
            return response()->json([
                'success' => false,
                'message' => "Cannot mark attendance before Namaz Start Date ({$userStart->format('d-m-Y')}).",
            ], 422);
        }

        if (! empty($validated['status']) && ! $this->namazService->hasPrayerTimeArrived($validated['attendance_date'], $validated['prayer'])) {
            $label = $this->namazService->getPrayerLabel($validated['prayer'], $validated['attendance_date']);
            return response()->json([
                'success' => false,
                'message' => "Namaz ka waqt nahi hua. {$label} ki attendance waqt aane ke baad hi mark ki ja sakti hai.",
            ], 422);
        }

        $status = ! empty($validated['status']) ? $validated['status'] : null;

        $result = $this->namazService->updatePrayerStatus(
            $user,
            $validated['attendance_date'],
            $validated['prayer'],
            $status,
            auth()->id()
        );

        return response()->json($result);
    }

    public function updateStartDate(Request $request, User $user): JsonResponse
    {
        if (! $user->isMuslim()) {
            return response()->json([
                'success' => false,
                'message' => 'User must have Muslim role.',
            ], 422);
        }

        $validated = $request->validate([
            'namaz_start_date' => ['required', 'date'],
        ]);

        $user->update([
            'namaz_start_date' => $validated['namaz_start_date'],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Namaz Start Date set to " . Carbon::parse($validated['namaz_start_date'])->format('d-m-Y') . " for {$user->name}.",
            'namaz_start_date' => $user->namaz_start_date?->format('d-m-Y'),
        ]);
    }

    public function updateDay(Request $request): JsonResponse
    {
        $allowedStatuses = [
            NamazAttendanceService::STATUS_JAMAT,
            NamazAttendanceService::STATUS_WITHOUT_JAMAT,
            NamazAttendanceService::STATUS_KAZA,
            NamazAttendanceService::STATUS_ABSENT,
        ];

        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'attendance_date' => ['required', 'date_format:Y-m-d'],
            'fajr_status' => ['nullable', 'string', Rule::in($allowedStatuses)],
            'zuhr_status' => ['nullable', 'string', Rule::in($allowedStatuses)],
            'asr_status' => ['nullable', 'string', Rule::in($allowedStatuses)],
            'maghrib_status' => ['nullable', 'string', Rule::in($allowedStatuses)],
            'isha_status' => ['nullable', 'string', Rule::in($allowedStatuses)],
        ]);

        $user = User::query()->muslim()->findOrFail($validated['user_id']);

        if (! $user->namaz_start_date) {
            return response()->json([
                'success' => false,
                'message' => "Namaz attendance has not been started for {$user->name}.",
            ], 422);
        }

        foreach (NamazAttendanceService::PRAYERS as $p) {
            $st = $validated["{$p}_status"] ?? null;
            if (! empty($st) && ! $this->namazService->hasPrayerTimeArrived($validated['attendance_date'], $p)) {
                $label = $this->namazService->getPrayerLabel($p, $validated['attendance_date']);
                return response()->json([
                    'success' => false,
                    'message' => "Namaz ka waqt nahi hua. {$label} ki attendance waqt aane ke baad hi mark ki ja sakti hai.",
                ], 422);
            }
        }

        $attendance = NamazAttendance::query()
            ->where('user_id', $user->id)
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->first();

        if (! $attendance) {
            $attendance = new NamazAttendance([
                'user_id' => $user->id,
                'attendance_date' => $validated['attendance_date'],
                'created_by' => auth()->id(),
            ]);
        }

        $attendance->fajr_status = $validated['fajr_status'] ?? null;
        $attendance->zuhr_status = $validated['zuhr_status'] ?? null;
        $attendance->asr_status = $validated['asr_status'] ?? null;
        $attendance->maghrib_status = $validated['maghrib_status'] ?? null;
        $attendance->isha_status = $validated['isha_status'] ?? null;
        $attendance->updated_by = auth()->id();

        // If all 5 statuses are null, delete
        $allNull = ! ($attendance->fajr_status || $attendance->zuhr_status || $attendance->asr_status || $attendance->maghrib_status || $attendance->isha_status);

        if ($allNull && $attendance->exists) {
            $attendance->delete();
        } else {
            $attendance->save();
        }

        return response()->json([
            'success' => true,
            'message' => "Attendance for " . Carbon::parse($validated['attendance_date'])->format('d-m-Y') . " updated successfully.",
        ]);
    }

    public function destroy(NamazAttendance $attendance): JsonResponse
    {
        $date = $attendance->attendance_date->format('d-m-Y');
        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => "Attendance record for {$date} reset successfully.",
        ]);
    }
}
