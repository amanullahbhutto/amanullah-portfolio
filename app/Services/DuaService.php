<?php

namespace App\Services;

use App\Models\Dua;
use App\Models\User;
use App\Models\UserDailyDua;
use App\Models\UserDuaProgress;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DuaService
{
    public function getTimezone(): string
    {
        return config('app.timezone', 'Asia/Karachi');
    }

    public function now(): Carbon
    {
        return Carbon::now($this->getTimezone());
    }

    public function getOrCreateProgress(
        User $user,
        Dua $dua,
        CarbonInterface|string|null $startDate = null
    ): UserDuaProgress {
        $tz = $this->getTimezone();
        $defaultStartDate = $startDate
            ? ($startDate instanceof CarbonInterface ? $startDate->format('Y-m-d') : Carbon::parse($startDate, $tz)->format('Y-m-d'))
            : $this->now()->format('Y-m-d');

        return UserDuaProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'dua_id' => $dua->id,
            ],
            [
                'total_completed' => 0,
                'tracking_start_date' => $defaultStartDate,
                'last_dua_at' => null,
            ]
        );
    }

    public function resolveOverallJourneyStartDate(User $user): Carbon
    {
        $tz = $this->getTimezone();

        if (Schema::hasTable('user_dua_progress')) {
            $earliestProgress = UserDuaProgress::where('user_id', $user->id)
                ->whereNotNull('tracking_start_date')
                ->orderBy('tracking_start_date', 'asc')
                ->value('tracking_start_date');

            if ($earliestProgress) {
                return Carbon::parse($earliestProgress, $tz)->startOfDay();
            }
        }

        return $user->created_at
            ? Carbon::parse($user->created_at, $tz)->startOfDay()
            : $this->now()->startOfDay();
    }

    public function formatJourneyDuration(Carbon $startDate, Carbon $currentDate): array
    {
        $diff = $startDate->diff($currentDate);
        $years = (int) $diff->y;
        $months = (int) $diff->m;
        $days = (int) $diff->d;

        $totalDays = max(1, (int) $startDate->diffInDays($currentDate) + 1);

        $parts = [];
        if ($years > 0) {
            $parts[] = $years . ' ' . ($years === 1 ? 'Year' : 'Years');
        }
        if ($months > 0) {
            $parts[] = $months . ' ' . ($months === 1 ? 'Month' : 'Months');
        }
        $parts[] = $days . ' ' . ($days === 1 ? 'Day' : 'Days');

        return [
            'years' => $years,
            'months' => $months,
            'days' => $days,
            'total_days' => $totalDays,
            'formatted_full' => implode(', ', $parts),
            'start_date_formatted' => $startDate->format('d M, Y'),
        ];
    }

    public function getTodayCompleted(User $user, int $duaId, Carbon $today): int
    {
        $todayStr = $today->format('Y-m-d');
        if (Schema::hasTable('user_daily_duas')) {
            $count = UserDailyDua::where('user_id', $user->id)
                ->where('dua_id', $duaId)
                ->where('date', $todayStr)
                ->value('count');

            if ($count !== null) {
                return (int) $count;
            }
        }

        return 0;
    }

    public function calculateDuaStats(
        User $user,
        Dua $dua,
        ?UserDuaProgress $progress = null
    ): array {
        $now = $this->now();
        $today = $now->copy()->startOfDay();

        if (! $progress) {
            $progress = $this->getOrCreateProgress($user, $dua);
        }

        $startDate = Carbon::parse($progress->tracking_start_date, $this->getTimezone())->startOfDay();
        $activeDays = max(1, (int) $startDate->diffInDays($today) + 1);

        $dailyTarget = (int) $dua->daily_target;
        $totalRequired = $activeDays * $dailyTarget;
        $totalCompleted = (int) $progress->total_completed;

        $todayCompleted = $this->getTodayCompleted($user, $dua->id, $today);

        $diff = $totalCompleted - $totalRequired;
        $remaining = max(0, $totalRequired - $totalCompleted);
        $extra = max(0, $diff);

        $percentage = $totalRequired > 0
            ? min(100, (int) round(($totalCompleted / $totalRequired) * 100))
            : ($totalCompleted > 0 ? 100 : 0);

        if ($extra > 0) {
            $statusLabel = "+{$extra} Extra";
            $statusBadge = 'extra';
        } elseif ($diff === 0) {
            $statusLabel = 'Completed';
            $statusBadge = 'completed';
        } else {
            $statusLabel = "Remaining {$remaining}";
            $statusBadge = 'behind';
        }

        return [
            'dua_id' => $dua->id,
            'title' => $dua->title,
            'arabic_text' => $dua->arabic_text,
            'urdu_meaning' => $dua->urdu_meaning,
            'daily_target' => $dailyTarget,
            'sort_order' => (int) $dua->sort_order,
            'is_active' => (bool) $dua->is_active,
            'description' => $dua->description,
            'reference' => $dua->reference,
            'active_days' => $activeDays,
            'total_required' => $totalRequired,
            'total_completed' => $totalCompleted,
            'today_completed' => $todayCompleted,
            'remaining' => $remaining,
            'extra' => $extra,
            'percentage' => $percentage,
            'status_label' => $statusLabel,
            'status_badge' => $statusBadge,
            'tracking_start_date' => $startDate->format('Y-m-d'),
            'formatted_start_date' => $startDate->format('d M, Y'),
            'formatted_last_dua' => $progress->last_dua_at
                ? Carbon::parse($progress->last_dua_at, $this->getTimezone())->diffForHumans()
                : 'Never',
        ];
    }

    public function getDashboardSummary(User $user): array
    {
        $now = $this->now();
        $today = $now->copy()->startOfDay();

        $duas = Dua::query()->ordered()->get();
        $progressMap = $user->duaProgress()->get()->keyBy('dua_id');

        $items = [];
        $todayTargetSum = 0;
        $todayCompletedSum = 0;
        $totalRequiredSum = 0;
        $totalCompletedSum = 0;

        foreach ($duas as $dua) {
            $progress = $progressMap->get($dua->id);
            $stats = $this->calculateDuaStats($user, $dua, $progress);
            $items[] = $stats;

            if ($dua->is_active) {
                $todayTargetSum += $stats['daily_target'];
                $todayCompletedSum += $stats['today_completed'];
                $totalRequiredSum += $stats['total_required'];
                $totalCompletedSum += $stats['total_completed'];
            }
        }

        $overallStartDate = $this->resolveOverallJourneyStartDate($user);
        $journeyDuration = $this->formatJourneyDuration($overallStartDate, $today);

        $todayPct = $todayTargetSum > 0
            ? min(100, (int) round(($todayCompletedSum / $todayTargetSum) * 100))
            : ($todayCompletedSum > 0 ? 100 : 0);

        return [
            'duas' => $items,
            'total_duas' => count($items),
            'active_duas' => collect($items)->where('is_active', true)->count(),
            'today_target_sum' => $todayTargetSum,
            'today_completed_sum' => $todayCompletedSum,
            'today_percentage' => $todayPct,
            'total_required_sum' => $totalRequiredSum,
            'total_completed_sum' => $totalCompletedSum,
            'journey_duration' => $journeyDuration,
        ];
    }

    public function increment(User $user, Dua $dua, int $countDelta = 1, ?CarbonInterface $clientDate = null): array
    {
        $now = $this->now();
        $today = $clientDate ? Carbon::instance($clientDate)->startOfDay() : $now->copy()->startOfDay();
        $todayStr = $today->format('Y-m-d');

        return DB::transaction(function () use ($user, $dua, $countDelta, $now, $today, $todayStr) {
            $progress = $this->getOrCreateProgress($user, $dua);
            $progress->total_completed = max(0, $progress->total_completed + $countDelta);
            $progress->last_dua_at = $now;
            $progress->save();

            // Daily entry
            $daily = UserDailyDua::firstOrCreate(
                ['user_id' => $user->id, 'dua_id' => $dua->id, 'date' => $todayStr],
                ['count' => 0]
            );
            $daily->count = max(0, $daily->count + $countDelta);
            $daily->save();

            return $this->calculateDuaStats($user, $dua, $progress);
        });
    }

    public function manual(User $user, Dua $dua, int $manualCount, ?CarbonInterface $clientDate = null): array
    {
        return $this->increment($user, $dua, $manualCount, $clientDate);
    }

    public function completeToday(User $user, Dua $dua): array
    {
        $now = $this->now();
        $today = $now->copy()->startOfDay();
        $todayStr = $today->format('Y-m-d');
        $target = (int) $dua->daily_target;

        return DB::transaction(function () use ($user, $dua, $now, $today, $todayStr, $target) {
            $progress = $this->getOrCreateProgress($user, $dua);
            $currentToday = $this->getTodayCompleted($user, $dua->id, $today);
            $needed = max(0, $target - $currentToday);

            if ($needed > 0) {
                $progress->total_completed += $needed;
                $progress->last_dua_at = $now;
                $progress->save();

                $daily = UserDailyDua::firstOrCreate(
                    ['user_id' => $user->id, 'dua_id' => $dua->id, 'date' => $todayStr],
                    ['count' => 0]
                );
                $daily->count = $target;
                $daily->save();
            }

            return $this->calculateDuaStats($user, $dua, $progress);
        });
    }

    public function completeAllToday(User $user): array
    {
        $duas = Dua::query()->active()->get();
        foreach ($duas as $dua) {
            $this->completeToday($user, $dua);
        }

        return $this->getDashboardSummary($user);
    }

    public function reset(User $user, Dua $dua): array
    {
        $now = $this->now();
        $todayStr = $now->format('Y-m-d');

        return DB::transaction(function () use ($user, $dua, $now, $todayStr) {
            $progress = $this->getOrCreateProgress($user, $dua);
            $progress->total_completed = 0;
            $progress->tracking_start_date = $todayStr;
            $progress->last_dua_at = null;
            $progress->save();

            // Clear today's count
            UserDailyDua::where('user_id', $user->id)
                ->where('dua_id', $dua->id)
                ->where('date', $todayStr)
                ->update(['count' => 0]);

            return $this->calculateDuaStats($user, $dua, $progress);
        });
    }

    public function resetAll(User $user): array
    {
        $now = $this->now();
        $todayStr = $now->format('Y-m-d');

        DB::transaction(function () use ($user, $todayStr) {
            UserDuaProgress::where('user_id', $user->id)->update([
                'total_completed' => 0,
                'tracking_start_date' => $todayStr,
                'last_dua_at' => null,
            ]);

            UserDailyDua::where('user_id', $user->id)
                ->where('date', $todayStr)
                ->update(['count' => 0]);
        });

        return $this->getDashboardSummary($user);
    }

    public function updateStartDate(User $user, Dua $dua, string $startDate): array
    {
        $tz = $this->getTimezone();
        $parsed = Carbon::parse($startDate, $tz)->format('Y-m-d');

        $progress = $this->getOrCreateProgress($user, $dua);
        $progress->tracking_start_date = $parsed;
        $progress->save();

        return $this->calculateDuaStats($user, $dua, $progress);
    }
}

