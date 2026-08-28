<?php

namespace App\Services;

use App\Models\NamazAttendance;
use App\Models\NamazSetting;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class NamazAttendanceService
{
    public const STATUS_JAMAT = 'jamat';
    public const STATUS_WITHOUT_JAMAT = 'without_jamat';
    public const STATUS_KAZA = 'kaza';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_PENDING = 'pending';
    public const STATUS_NOT_APPLICABLE = 'not_applicable';

    public const PRAYERS = ['fajr', 'zuhr', 'asr', 'maghrib', 'isha'];

    protected ?NamazSetting $settings = null;

    public function getSettings(): NamazSetting
    {
        if ($this->settings === null) {
            $this->settings = NamazSetting::getSettings();
        }

        return $this->settings;
    }

    public function getTimezone(): string
    {
        return config('app.timezone', 'Asia/Karachi');
    }

    public function now(): Carbon
    {
        return Carbon::now($this->getTimezone());
    }

    public function isFriday(CarbonInterface|string $date): bool
    {
        $carbon = $date instanceof CarbonInterface ? $date : Carbon::parse($date, $this->getTimezone());
        return $carbon->isFriday();
    }

    public function getPrayerLabel(string $prayerKey, CarbonInterface|string $date): string
    {
        if ($prayerKey === 'zuhr' && $this->isFriday($date)) {
            return "Jumu'ah";
        }

        return match ($prayerKey) {
            'fajr' => 'Fajr',
            'zuhr' => 'Zuhr',
            'asr' => 'Asr',
            'maghrib' => 'Maghrib',
            'isha' => 'Isha',
            default => ucfirst($prayerKey),
        };
    }

    public function getStatusMeta(string $status, ?string $prayerLabel = null): array
    {
        return match ($status) {
            self::STATUS_JAMAT => [
                'key' => self::STATUS_JAMAT,
                'label' => 'Jamat',
                'badge_class' => 'badge bg-success-subtle text-success',
                'btn_class' => 'btn-outline-success',
                'icon' => 'bi bi-check-circle-fill',
                'color' => 'success',
            ],
            self::STATUS_WITHOUT_JAMAT => [
                'key' => self::STATUS_WITHOUT_JAMAT,
                'label' => 'Without Jamat',
                'badge_class' => 'badge bg-info-subtle text-info',
                'btn_class' => 'btn-outline-info',
                'icon' => 'bi bi-person-fill',
                'color' => 'info',
            ],
            self::STATUS_KAZA => [
                'key' => self::STATUS_KAZA,
                'label' => 'Kaza',
                'badge_class' => 'badge bg-warning-subtle text-warning',
                'btn_class' => 'btn-outline-warning',
                'icon' => 'bi bi-clock-history',
                'color' => 'warning',
            ],
            self::STATUS_ABSENT => [
                'key' => self::STATUS_ABSENT,
                'label' => 'Absent',
                'badge_class' => 'badge bg-danger-subtle text-danger',
                'btn_class' => 'btn-outline-danger',
                'icon' => 'bi bi-x-circle-fill',
                'color' => 'danger',
            ],
            self::STATUS_PENDING => [
                'key' => self::STATUS_PENDING,
                'label' => 'Pending',
                'badge_class' => 'badge bg-secondary-subtle text-secondary',
                'btn_class' => 'btn-outline-secondary',
                'icon' => 'bi bi-hourglass-split',
                'color' => 'secondary',
            ],
            default => [
                'key' => self::STATUS_NOT_APPLICABLE,
                'label' => '-',
                'badge_class' => 'badge bg-secondary-subtle text-muted-custom',
                'btn_class' => 'btn-outline-secondary',
                'icon' => 'bi bi-dash',
                'color' => 'muted',
            ],
        };
    }

    /**
     * Resolves the effective prayer status according to the specified priority rules:
     * 1. Manually saved status in database (jamat, without_jamat, kaza, absent)
     * 2. Before Namaz Start Date -> not_applicable
     * 3. Future date/time -> pending
     * 4. Past date/time with no manual entry -> kaza (auto)
     */
    public function getEffectivePrayerStatus(
        User $user,
        CarbonInterface|string $date,
        string $prayerKey,
        ?string $manualStatus = null
    ): array {
        // Priority 1: Manually stored status
        if ($manualStatus && in_array($manualStatus, [self::STATUS_JAMAT, self::STATUS_WITHOUT_JAMAT, self::STATUS_KAZA, self::STATUS_ABSENT], true)) {
            $meta = $this->getStatusMeta($manualStatus);
            $meta['is_manual'] = true;
            $meta['is_pending'] = false;
            $meta['is_auto_kaza'] = false;
            return $meta;
        }

        $carbonDate = ($date instanceof CarbonInterface ? $date->copy() : Carbon::parse($date))->setTimezone($this->getTimezone())->startOfDay();

        // Priority 2: Before user's Namaz Start Date
        if (! $user->namaz_start_date) {
            $meta = $this->getStatusMeta(self::STATUS_NOT_APPLICABLE);
            $meta['is_manual'] = false;
            $meta['is_pending'] = false;
            $meta['is_auto_kaza'] = false;
            return $meta;
        }

        $startDate = Carbon::parse($user->namaz_start_date, $this->getTimezone())->startOfDay();
        if ($carbonDate->lt($startDate)) {
            $meta = $this->getStatusMeta(self::STATUS_NOT_APPLICABLE);
            $meta['is_manual'] = false;
            $meta['is_pending'] = false;
            $meta['is_auto_kaza'] = false;
            return $meta;
        }

        $now = $this->now();
        $today = $now->copy()->startOfDay();

        // If date is strictly in the future (> today)
        if ($carbonDate->gt($today)) {
            $meta = $this->getStatusMeta(self::STATUS_PENDING);
            $meta['is_manual'] = false;
            $meta['is_pending'] = true;
            $meta['is_auto_kaza'] = false;
            return $meta;
        }

        // If date is in the past (< today)
        if ($carbonDate->lt($today)) {
            $meta = $this->getStatusMeta(self::STATUS_KAZA);
            $meta['is_manual'] = false;
            $meta['is_pending'] = false;
            $meta['is_auto_kaza'] = true;
            return $meta;
        }

        // If date is Today, compare current time against prayer time
        $settings = $this->getSettings();
        $prayerTimeString = $settings->getTimeForPrayer($prayerKey, $carbonDate);
        [$hour, $minute] = explode(':', $prayerTimeString) + [0, 0];

        $prayerDateTime = $carbonDate->copy()->setTime((int) $hour, (int) $minute, 0);

        if ($now->lt($prayerDateTime)) {
            $meta = $this->getStatusMeta(self::STATUS_PENDING);
            $meta['is_manual'] = false;
            $meta['is_pending'] = true;
            $meta['is_auto_kaza'] = false;
            return $meta;
        }

        // Prayer time has arrived/passed without manual record -> Auto Kaza
        $meta = $this->getStatusMeta(self::STATUS_KAZA);
        $meta['is_manual'] = false;
        $meta['is_pending'] = false;
        $meta['is_auto_kaza'] = true;
        return $meta;
    }

    public function hasPrayerTimeArrived(CarbonInterface|string $date, string $prayerKey): bool
    {
        $tz = $this->getTimezone();
        $carbonDate = ($date instanceof CarbonInterface ? $date->copy() : Carbon::parse($date))->setTimezone($tz)->startOfDay();
        $now = $this->now();
        $today = $now->copy()->startOfDay();

        if ($carbonDate->gt($today)) {
            return false;
        }

        if ($carbonDate->lt($today)) {
            return true;
        }

        $settings = $this->getSettings();
        $prayerTimeString = $settings->getTimeForPrayer($prayerKey, $carbonDate);
        [$hour, $minute] = explode(':', $prayerTimeString) + [0, 0];
        $prayerDateTime = $carbonDate->copy()->setTime((int) $hour, (int) $minute, 0);

        return $now->gte($prayerDateTime);
    }

    /**
     * Generates a lazy, unified daily attendance ledger for a Muslim user across a date range.
     */
    public function generateAttendanceLedger(
        User $user,
        CarbonInterface|string $startDate,
        CarbonInterface|string $endDate
    ): Collection {
        if (! $user->namaz_start_date) {
            return collect();
        }

        $tz = $this->getTimezone();
        $startCarbon = ($startDate instanceof CarbonInterface ? $startDate->copy() : Carbon::parse($startDate))->setTimezone($tz)->startOfDay();
        $endCarbon = ($endDate instanceof CarbonInterface ? $endDate->copy() : Carbon::parse($endDate))->setTimezone($tz)->startOfDay();
        $userStart = Carbon::parse($user->namaz_start_date, $tz)->startOfDay();

        $effectiveStart = $startCarbon->lt($userStart) ? $userStart : $startCarbon;

        if ($effectiveStart->gt($endCarbon)) {
            return collect();
        }

        $records = NamazAttendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('attendance_date', [$effectiveStart->format('Y-m-d'), $endCarbon->format('Y-m-d')])
            ->get()
            ->keyBy(fn ($item) => $item->attendance_date->format('Y-m-d'));

        $period = CarbonPeriod::create($effectiveStart, '1 day', $endCarbon);
        $dates = array_reverse(iterator_to_array($period)); // newest date first

        $ledger = collect();

        foreach ($dates as $date) {
            $dateString = $date->format('Y-m-d');
            $record = $records->get($dateString);

            $prayersData = [];
            $counts = [
                'jamat' => 0,
                'without_jamat' => 0,
                'kaza' => 0,
                'absent' => 0,
                'pending' => 0,
            ];

            foreach (self::PRAYERS as $prayerKey) {
                $manualStatus = $record ? $record->{"{$prayerKey}_status"} : null;
                $effective = $this->getEffectivePrayerStatus($user, $date, $prayerKey, $manualStatus);
                $label = $this->getPrayerLabel($prayerKey, $date);
                $hasArrived = $this->hasPrayerTimeArrived($date, $prayerKey);

                $prayersData[$prayerKey] = array_merge($effective, [
                    'prayer_key' => $prayerKey,
                    'prayer_label' => $label,
                    'manual_status' => $manualStatus,
                    'has_arrived' => $hasArrived,
                ]);

                if (isset($counts[$effective['key']])) {
                    $counts[$effective['key']]++;
                }
            }

            $ledger->push([
                'user_id' => $user->id,
                'attendance_id' => $record?->id,
                'date' => $date,
                'date_string' => $dateString,
                'formatted_date' => $date->format('d-m-Y'),
                'day_name' => $date->format('l'),
                'is_friday' => $date->isFriday(),
                'is_today' => $date->isToday(),
                'prayers' => $prayersData,
                'counts' => $counts,
                'record' => $record,
            ]);
        }

        return $ledger;
    }

    /**
     * Updates or resets a single prayer status for a person on a specific date via AJAX.
     */
    public function updatePrayerStatus(
        User $user,
        string $date,
        string $prayerKey,
        ?string $status,
        ?int $authUserId = null
    ): array {
        if (! in_array($prayerKey, self::PRAYERS, true)) {
            throw new \InvalidArgumentException("Invalid prayer key: {$prayerKey}");
        }

        if ($status !== null && ! in_array($status, [self::STATUS_JAMAT, self::STATUS_WITHOUT_JAMAT, self::STATUS_KAZA, self::STATUS_ABSENT], true)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        $column = "{$prayerKey}_status";

        $attendance = NamazAttendance::query()
            ->where('user_id', $user->id)
            ->whereDate('attendance_date', $date)
            ->first();

        if (! $attendance) {
            $attendance = new NamazAttendance([
                'user_id' => $user->id,
                'attendance_date' => $date,
                'created_by' => $authUserId,
            ]);
        }

        $attendance->{$column} = $status;
        $attendance->updated_by = $authUserId;

        // If all 5 statuses become null and record exists, delete the row
        $allNull = true;
        foreach (self::PRAYERS as $p) {
            if ($attendance->{"{$p}_status"} !== null) {
                $allNull = false;
                break;
            }
        }

        if ($allNull && $attendance->exists) {
            $attendance->delete();
            $attendance = null;
        } else {
            $attendance->save();
        }

        $effective = $this->getEffectivePrayerStatus($user, $date, $prayerKey, $status);
        $label = $this->getPrayerLabel($prayerKey, $date);

        return [
            'success' => true,
            'attendance' => $attendance,
            'prayer_key' => $prayerKey,
            'prayer_label' => $label,
            'status' => $effective,
            'message' => "{$label} attendance updated to {$effective['label']}.",
        ];
    }

    /**
     * Calculates comprehensive dashboard statistics for all Muslim users or an individual user.
     */
    public function calculateDashboardStatistics(
        ?User $selectedUser,
        CarbonInterface|string $startDate,
        CarbonInterface|string $endDate
    ): array {
        $tz = $this->getTimezone();
        $startCarbon = ($startDate instanceof CarbonInterface ? $startDate->copy() : Carbon::parse($startDate))->setTimezone($tz)->startOfDay();
        $endCarbon = ($endDate instanceof CarbonInterface ? $endDate->copy() : Carbon::parse($endDate))->setTimezone($tz)->startOfDay();

        $muslimQuery = User::query()->muslim();
        if ($selectedUser) {
            $muslimQuery->where('id', $selectedUser->id);
        }
        $muslimUsers = $muslimQuery->get();

        $overall = [
            'total_namaz' => 0,
            'jamat' => 0,
            'without_jamat' => 0,
            'kaza' => 0,
            'absent' => 0,
            'pending' => 0,
            'jamat_percentage' => 0,
        ];

        $perPrayer = [];
        foreach (self::PRAYERS as $p) {
            $perPrayer[$p] = [
                'key' => $p,
                'label' => $p === 'zuhr' ? 'Zuhr / Jumu\'ah' : ucfirst($p),
                'total' => 0,
                'jamat' => 0,
                'without_jamat' => 0,
                'kaza' => 0,
                'absent' => 0,
                'pending' => 0,
                'jamat_percentage' => 0,
            ];
        }

        foreach ($muslimUsers as $user) {
            if (! $user->namaz_start_date) {
                continue;
            }

            $userStart = Carbon::parse($user->namaz_start_date, $tz)->startOfDay();
            $effectiveStart = $startCarbon->lt($userStart) ? $userStart : $startCarbon;
            if ($effectiveStart->gt($endCarbon)) {
                continue;
            }

            $records = NamazAttendance::query()
                ->where('user_id', $user->id)
                ->whereBetween('attendance_date', [$effectiveStart->format('Y-m-d'), $endCarbon->format('Y-m-d')])
                ->get()
                ->keyBy(fn ($item) => $item->attendance_date->format('Y-m-d'));

            $period = CarbonPeriod::create($effectiveStart, '1 day', $endCarbon);

            foreach ($period as $day) {
                $dateString = $day->format('Y-m-d');
                $record = $records->get($dateString);

                foreach (self::PRAYERS as $prayerKey) {
                    $manualStatus = $record ? $record->{"{$prayerKey}_status"} : null;
                    $status = $this->getEffectivePrayerStatus($user, $day, $prayerKey, $manualStatus);
                    $key = $status['key'];

                    if ($key === self::STATUS_NOT_APPLICABLE) {
                        continue;
                    }

                    if ($key === self::STATUS_PENDING) {
                        $overall['pending']++;
                        $perPrayer[$prayerKey]['pending']++;
                        continue;
                    }

                    // Completed prayer opportunity
                    $overall['total_namaz']++;
                    $perPrayer[$prayerKey]['total']++;

                    if (isset($overall[$key])) {
                        $overall[$key]++;
                    }
                    if (isset($perPrayer[$prayerKey][$key])) {
                        $perPrayer[$prayerKey][$key]++;
                    }
                }
            }
        }

        // Calculate percentages
        if ($overall['total_namaz'] > 0) {
            $overall['jamat_percentage'] = round(($overall['jamat'] / $overall['total_namaz']) * 100, 1);
        }

        foreach (self::PRAYERS as $p) {
            if ($perPrayer[$p]['total'] > 0) {
                $perPrayer[$p]['jamat_percentage'] = round(($perPrayer[$p]['jamat'] / $perPrayer[$p]['total']) * 100, 1);
            }
        }

        $personInfo = null;
        if ($selectedUser) {
            $trackedDays = 0;
            if ($selectedUser->namaz_start_date) {
                $start = Carbon::parse($selectedUser->namaz_start_date, $tz)->startOfDay();
                $now = $this->now()->startOfDay();
                $trackedDays = $start->lte($now) ? $start->diffInDays($now) + 1 : 0;
            }

            $personInfo = [
                'id' => $selectedUser->id,
                'name' => $selectedUser->name,
                'email' => $selectedUser->email,
                'role' => 'Muslim',
                'namaz_start_date' => $selectedUser->namaz_start_date?->format('d-m-Y'),
                'namaz_start_date_raw' => $selectedUser->namaz_start_date?->format('Y-m-d'),
                'total_tracked_days' => $trackedDays,
            ];
        }

        return [
            'overall' => $overall,
            'per_prayer' => $perPrayer,
            'person_info' => $personInfo,
            'start_date' => $startCarbon->format('Y-m-d'),
            'end_date' => $endCarbon->format('Y-m-d'),
            'total_muslim_users' => $muslimUsers->count(),
        ];
    }
}

