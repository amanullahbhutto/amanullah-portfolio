<?php

namespace App\Services;

use App\Models\Tasbeeh;
use App\Models\User;
use App\Models\UserDailyZikr;
use App\Models\UserLifetimeZikr;
use App\Models\UserTasbeehProgress;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ZikrService
{
    public function getTimezone(): string
    {
        return config('app.timezone', 'Asia/Karachi');
    }

    public function now(): Carbon
    {
        return Carbon::now($this->getTimezone());
    }

    /**
     * Gets existing active progress or creates a new one starting today.
     */
    public function getOrCreateProgress(
        User $user,
        Tasbeeh $tasbeeh,
        CarbonInterface|string|null $startDate = null
    ): UserTasbeehProgress {
        $tz = $this->getTimezone();
        $defaultStartDate = $startDate
            ? ($startDate instanceof CarbonInterface ? $startDate->format('Y-m-d') : Carbon::parse($startDate, $tz)->format('Y-m-d'))
            : $this->now()->format('Y-m-d');

        return UserTasbeehProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'tasbeeh_id' => $tasbeeh->id,
            ],
            [
                'total_completed' => 0,
                'tracking_start_date' => $defaultStartDate,
                'last_zikr_at' => null,
            ]
        );
    }

    /**
     * Resolves the earliest overall Zikr Journey start date across all Tasbeeh progress records.
     */
    public function resolveOverallJourneyStartDate(User $user): Carbon
    {
        $tz = $this->getTimezone();

        if (Schema::hasTable('user_tasbeeh_progress')) {
            $earliestProgress = UserTasbeehProgress::where('user_id', $user->id)
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

    /**
     * Resolves the independent start date for the Lifetime Total Zikr counter.
     */
    public function resolveLifetimeStartDate(User $user, ?UserLifetimeZikr $lifetimeRecord = null): Carbon
    {
        $tz = $this->getTimezone();

        if ($lifetimeRecord && $lifetimeRecord->started_at) {
            return Carbon::parse($lifetimeRecord->started_at, $tz)->startOfDay();
        }

        if (Schema::hasTable('user_lifetime_zikrs')) {
            $record = UserLifetimeZikr::where('user_id', $user->id)->first();
            if ($record && $record->started_at) {
                return Carbon::parse($record->started_at, $tz)->startOfDay();
            }
        }

        return $this->resolveOverallJourneyStartDate($user);
    }

    /**
     * Formats duration into Days, Months, and Years.
     */
    public function formatDurationParts(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        if ($start->gt($end)) {
            return [
                'total_days' => 1,
                'years' => 0,
                'months' => 0,
                'days' => 1,
                'formatted_short' => '1D',
                'formatted_full' => '1 Day',
                'formatted_badge' => 'Day 1',
                'start_date_formatted' => $start->format('d M, Y'),
                'start_date_iso' => $start->format('Y-m-d'),
            ];
        }

        $totalDays = (int) $start->diffInDays($end) + 1;

        // Calculate diff with 1 day added so day 1 shows 1 day
        $diff = $start->diff($end->copy()->addDay());
        $years = (int) $diff->y;
        $months = (int) $diff->m;
        $days = (int) $diff->d;

        $parts = [];
        if ($years > 0) {
            $parts[] = $years . ' ' . ($years === 1 ? 'Year' : 'Years');
        }
        if ($months > 0) {
            $parts[] = $months . ' ' . ($months === 1 ? 'Month' : 'Months');
        }
        if ($days > 0 || empty($parts)) {
            $parts[] = $days . ' ' . ($days === 1 ? 'Day' : 'Days');
        }

        $shortParts = [];
        if ($years > 0) $shortParts[] = "{$years}Y";
        if ($months > 0) $shortParts[] = "{$months}M";
        if ($days > 0 || empty($shortParts)) $shortParts[] = "{$days}D";

        return [
            'total_days' => $totalDays,
            'years' => $years,
            'months' => $months,
            'days' => $days,
            'formatted_short' => implode(' ', $shortParts),
            'formatted_full' => implode(', ', $parts),
            'formatted_badge' => $totalDays === 1 ? 'Day 1' : "{$totalDays} Days",
            'start_date_formatted' => $start->format('d M, Y'),
            'start_date_iso' => $start->format('Y-m-d'),
        ];
    }

    /**
     * Gets or initializes the persistent lifetime/all-time Zikr record for a user.
     * This record is completely independent and never reset by standard cycle resets or tasbeeh deletions.
     */
    public function getOrCreateLifetimeRecord(User $user): UserLifetimeZikr
    {
        $defaultStart = $this->resolveLifetimeStartDate($user);

        if (!Schema::hasTable('user_lifetime_zikrs')) {
            $dummy = new UserLifetimeZikr();
            $dummy->user_id = $user->id;
            $dummy->lifetime_count = 0;
            $dummy->started_at = $defaultStart;
            $dummy->last_zikr_at = null;
            return $dummy;
        }

        return UserLifetimeZikr::firstOrCreate(
            ['user_id' => $user->id],
            [
                'lifetime_count' => 0,
                'started_at' => $defaultStart,
                'last_zikr_at' => null,
            ]
        );
    }

    /**
     * Calculates statistics and cumulative backlog for a single Tasbeeh.
     */
    public function calculateTasbeehStats(
        User $user,
        Tasbeeh $tasbeeh,
        ?UserTasbeehProgress $progress = null,
        ?int $todayCompletedOverride = null
    ): array {
        $tz = $this->getTimezone();
        $now = $this->now();
        $today = $now->copy()->startOfDay();

        if (! $progress) {
            $progress = UserTasbeehProgress::where('user_id', $user->id)
                ->where('tasbeeh_id', $tasbeeh->id)
                ->first();
        }

        $startDateCarbon = $progress
            ? Carbon::parse($progress->tracking_start_date, $tz)->startOfDay()
            : $today->copy();

        // Calculate active days from tracking_start_date to today inclusive
        if ($startDateCarbon->gt($today)) {
            $activeDays = 0;
        } else {
            $activeDays = (int) $startDateCarbon->diffInDays($today) + 1;
        }

        $dailyTarget = (int) $tasbeeh->daily_target;
        $totalRequired = $activeDays * $dailyTarget;
        $totalCompleted = $progress ? (int) $progress->total_completed : 0;
        $remaining = max($totalRequired - $totalCompleted, 0);
        $extra = max($totalCompleted - $totalRequired, 0);

        // Calculate 24-hour daily count for today's calendar date
        if ($todayCompletedOverride !== null) {
            $todayCompleted = $todayCompletedOverride;
        } elseif (Schema::hasTable('user_daily_zikrs')) {
            $todayDate = $today->format('Y-m-d');
            $dailyRecord = UserDailyZikr::where('user_id', $user->id)
                ->where('tasbeeh_id', $tasbeeh->id)
                ->where('date', $todayDate)
                ->first();
            $todayCompleted = $dailyRecord ? (int) $dailyRecord->count : 0;
        } else {
            $todayCompleted = 0;
        }

        $todayExtra = max($todayCompleted - $dailyTarget, 0);
        $todayRemaining = max($dailyTarget - $todayCompleted, 0);
        $todayPercentage = $dailyTarget > 0 ? min(round(($todayCompleted / $dailyTarget) * 100, 1), 100) : 100;

        if ($totalRequired > 0) {
            $percentage = min(round(($totalCompleted / $totalRequired) * 100, 1), 100);
        } else {
            $percentage = $totalCompleted > 0 ? 100 : 0;
        }

        $statusKey = 'backlog';
        $statusLabel = "{$remaining} Remaining";
        $statusBadge = 'bg-warning-subtle text-warning border-warning';

        if ($extra > 0) {
            $statusKey = 'extra';
            $statusLabel = "+{$extra} Extra";
            $statusBadge = 'bg-info-subtle text-info border-info';
        } elseif ($remaining === 0) {
            $statusKey = 'completed';
            $statusLabel = 'Target Completed';
            $statusBadge = 'bg-success-subtle text-success border-success';
        }

        return [
            'tasbeeh_id' => $tasbeeh->id,
            'title' => $tasbeeh->title,
            'arabic_text' => $tasbeeh->arabic_text,
            'urdu_meaning' => $tasbeeh->urdu_meaning,
            'daily_target' => $dailyTarget,
            'today_completed' => $todayCompleted,
            'today_target' => $dailyTarget,
            'today_remaining' => $todayRemaining,
            'today_extra' => $todayExtra,
            'today_percentage' => $todayPercentage,
            'tracking_start_date' => $startDateCarbon->format('Y-m-d'),
            'formatted_start_date' => $startDateCarbon->format('d M, Y'),
            'active_days' => $activeDays,
            'total_required' => $totalRequired,
            'total_completed' => $totalCompleted,
            'remaining' => $remaining,
            'backlog' => $remaining,
            'extra' => $extra,
            'percentage' => $percentage,
            'status_key' => $statusKey,
            'status_label' => $statusLabel,
            'status_badge' => $statusBadge,
            'last_zikr_at' => $progress?->last_zikr_at,
            'formatted_last_zikr' => $progress?->last_zikr_at ? Carbon::parse($progress->last_zikr_at, $tz)->diffForHumans() : null,
            'progress' => $progress,
        ];
    }

    /**
     * Aggregated Zikr dashboard summary across all active Tasbeehs for a Muslim user.
     */
    public function getDashboardSummary(User $user): array
    {
        $activeTasbeehs = Tasbeeh::query()->active()->ordered()->get();
        $progressMap = UserTasbeehProgress::where('user_id', $user->id)->get()->keyBy('tasbeeh_id');

        $todayDate = $this->now()->format('Y-m-d');
        $yesterdayDate = $this->now()->copy()->subDay()->format('Y-m-d');

        $hasDailyTable = Schema::hasTable('user_daily_zikrs');
        $todayDailyMap = $hasDailyTable
            ? UserDailyZikr::where('user_id', $user->id)
                ->where('date', $todayDate)
                ->pluck('count', 'tasbeeh_id')
            : collect();

        $yesterdayDailyMap = $hasDailyTable
            ? UserDailyZikr::where('user_id', $user->id)
                ->where('date', $yesterdayDate)
                ->pluck('count', 'tasbeeh_id')
            : collect();

        $tasbeehStats = [];
        $overallTodayRequired = 0;
        $overallTodayCompleted = 0;
        $overallTotalRequired = 0;
        $overallTotalCompleted = 0;
        $overallBacklog = 0;
        $overallExtra = 0;

        foreach ($activeTasbeehs as $tasbeeh) {
            $progress = $progressMap->get($tasbeeh->id);
            $todayCount = (int) ($todayDailyMap->get($tasbeeh->id) ?? 0);
            $stats = $this->calculateTasbeehStats($user, $tasbeeh, $progress, $todayCount);
            $tasbeehStats[] = $stats;

            $overallTodayRequired += $stats['daily_target'];
            $overallTodayCompleted += $stats['today_completed'];
            $overallTotalRequired += $stats['total_required'];
            $overallTotalCompleted += $stats['total_completed'];
            $overallBacklog += $stats['remaining'];
            $overallExtra += $stats['extra'];
        }

        $overallTodayPercentage = $overallTodayRequired > 0
            ? min(round(($overallTodayCompleted / $overallTodayRequired) * 100, 1), 100)
            : 100;

        $overallTodayExtra = max($overallTodayCompleted - $overallTodayRequired, 0);
        $overallTodayRemaining = max($overallTodayRequired - $overallTodayCompleted, 0);

        $overallYesterdayCompleted = $yesterdayDailyMap->sum() > 0
            ? (int) $yesterdayDailyMap->sum()
            : max($overallTotalCompleted - $overallTodayCompleted, 0);

        $overallPercentage = $overallTotalRequired > 0
            ? min(round(($overallTotalCompleted / $overallTotalRequired) * 100, 1), 100)
            : 100;

        $lifetimeRecord = $this->getOrCreateLifetimeRecord($user);

        // 1. Overall Zikr Journey (From oldest tasbeeh progress tracking date across all tables)
        $journeyStartDate = $this->resolveOverallJourneyStartDate($user);
        $journeyDuration = $this->formatDurationParts($journeyStartDate, $this->now());

        // 2. Lifetime Total (Independent start date, resets on password deletion)
        $lifetimeStartDate = $this->resolveLifetimeStartDate($user, $lifetimeRecord);
        $lifetimeDuration = $this->formatDurationParts($lifetimeStartDate, $this->now());

        return [
            'user' => $user,
            'journey_start_date' => $journeyStartDate->format('Y-m-d'),
            'journey_duration' => $journeyDuration,
            'lifetime_total' => (int) $lifetimeRecord->lifetime_count,
            'lifetime_started_at' => $lifetimeStartDate->format('Y-m-d'),
            'lifetime_duration' => $lifetimeDuration,
            'lifetime_last_zikr' => $lifetimeRecord->last_zikr_at ? Carbon::parse($lifetimeRecord->last_zikr_at, $this->getTimezone())->diffForHumans() : null,
            'total_active_tasbeehs' => $activeTasbeehs->count(),
            'overall_today_required' => $overallTodayRequired,
            'overall_today_completed' => $overallTodayCompleted,
            'overall_today_percentage' => $overallTodayPercentage,
            'overall_today_extra' => $overallTodayExtra,
            'overall_today_remaining' => $overallTodayRemaining,
            'overall_yesterday_completed' => $overallYesterdayCompleted,
            'overall_total_required' => $overallTotalRequired,
            'overall_total_completed' => $overallTotalCompleted,
            'overall_backlog' => $overallBacklog,
            'overall_extra' => $overallExtra,
            'overall_percentage' => $overallPercentage,
            'tasbeehs' => $tasbeehStats,
        ];
    }

    /**
     * Atomically adds or adjusts count to a user's single active Tasbeeh progress record.
     * Also seamlessly updates the persistent lifetime/all-time counter and 24-hour daily log.
     */
    public function addCount(User $user, Tasbeeh $tasbeeh, int $count, string $source = 'live'): array
    {
        if ($count === 0) {
            throw new \InvalidArgumentException('Zikr count cannot be zero.');
        }

        $progress = DB::transaction(function () use ($user, $tasbeeh, $count) {
            $record = $this->getOrCreateProgress($user, $tasbeeh);
            $lifetime = $this->getOrCreateLifetimeRecord($user);
            $today = $this->now()->format('Y-m-d');

            if ($count > 0) {
                // Atomic database increment for active cycle, lifetime and 24-hour daily log
                $record->increment('total_completed', $count);
                $record->update(['last_zikr_at' => $this->now()]);

                if ($lifetime->exists) {
                    $lifetime->increment('lifetime_count', $count);
                    $lifetime->update(['last_zikr_at' => $this->now()]);
                }

                if (Schema::hasTable('user_daily_zikrs')) {
                    $daily = UserDailyZikr::firstOrCreate(
                        ['user_id' => $user->id, 'tasbeeh_id' => $tasbeeh->id, 'date' => $today],
                        ['count' => 0]
                    );
                    $daily->increment('count', $count);
                }
            } else {
                // Subtraction / adjustment (ensure total does not drop below 0)
                $newTotal = max(((int) $record->total_completed) + $count, 0);
                $record->update([
                    'total_completed' => $newTotal,
                    'last_zikr_at' => $this->now(),
                ]);

                if ($lifetime->exists) {
                    $newLifetime = max(((int) $lifetime->lifetime_count) + $count, 0);
                    $lifetime->update([
                        'lifetime_count' => $newLifetime,
                        'last_zikr_at' => $this->now(),
                    ]);
                }

                if (Schema::hasTable('user_daily_zikrs')) {
                    $daily = UserDailyZikr::where('user_id', $user->id)
                        ->where('tasbeeh_id', $tasbeeh->id)
                        ->where('date', $today)
                        ->first();
                    if ($daily) {
                        $newDaily = max(((int) $daily->count) + $count, 0);
                        $daily->update(['count' => $newDaily]);
                    }
                }
            }

            return $record->fresh();
        });

        $stats = $this->calculateTasbeehStats($user, $tasbeeh, $progress);

        $message = $count > 0
            ? "+{$count} Zikr added successfully."
            : "{$count} Zikr adjusted successfully.";

        return [
            'success' => true,
            'message' => $message,
            'added_count' => $count,
            'source' => $source,
            'stats' => $stats,
        ];
    }

    /**
     * Resets ONLY the selected Tasbeeh's tracking progress for this user.
     * All other Tasbeeh records remain 100% untouched!
     */
    public function resetProgress(User $user, Tasbeeh $tasbeeh): array
    {
        $today = $this->now()->format('Y-m-d');

        $progress = DB::transaction(function () use ($user, $tasbeeh, $today) {
            $record = $this->getOrCreateProgress($user, $tasbeeh);

            $record->update([
                'total_completed' => 0,
                'tracking_start_date' => $today,
                'last_zikr_at' => null,
            ]);

            if (Schema::hasTable('user_daily_zikrs')) {
                UserDailyZikr::where('user_id', $user->id)
                    ->where('tasbeeh_id', $tasbeeh->id)
                    ->where('date', $today)
                    ->delete();
            }

            return $record->fresh();
        });

        $stats = $this->calculateTasbeehStats($user, $tasbeeh, $progress);

        return [
            'success' => true,
            'message' => "Tracking for {$tasbeeh->title} has been reset. New cycle started from today.",
            'stats' => $stats,
        ];
    }

    /**
     * Updates the custom tracking start date for a user's Tasbeeh.
     */
    public function updateStartDate(User $user, Tasbeeh $tasbeeh, string $startDate): array
    {
        $tz = $this->getTimezone();
        $parsed = Carbon::parse($startDate, $tz)->format('Y-m-d');

        $progress = DB::transaction(function () use ($user, $tasbeeh, $parsed) {
            $record = $this->getOrCreateProgress($user, $tasbeeh, $parsed);

            $record->update([
                'tracking_start_date' => $parsed,
            ]);

            return $record->fresh();
        });

        $stats = $this->calculateTasbeehStats($user, $tasbeeh, $progress);

        return [
            'success' => true,
            'message' => "Tracking start date updated to " . Carbon::parse($parsed)->format('d M, Y') . ".",
            'stats' => $stats,
        ];
    }

    /**
     * Marks a single Tasbeeh as completed for today for the user by adding its Daily Target.
     */
    public function completeSingleForToday(User $user, Tasbeeh $tasbeeh): array
    {
        $progress = $this->getOrCreateProgress($user, $tasbeeh);
        $countToAdd = max((int) $tasbeeh->daily_target, 1);

        DB::transaction(function () use ($user, $progress, $tasbeeh, $countToAdd) {
            $progress->increment('total_completed', $countToAdd);
            $progress->update(['last_zikr_at' => $this->now()]);

            $lifetime = $this->getOrCreateLifetimeRecord($user);
            if ($lifetime->exists) {
                $lifetime->increment('lifetime_count', $countToAdd);
                $lifetime->update(['last_zikr_at' => $this->now()]);
            }

            if (Schema::hasTable('user_daily_zikrs')) {
                $today = $this->now()->format('Y-m-d');
                $daily = UserDailyZikr::firstOrCreate(
                    ['user_id' => $user->id, 'tasbeeh_id' => $tasbeeh->id, 'date' => $today],
                    ['count' => 0]
                );
                $daily->increment('count', $countToAdd);
            }
        });

        $updatedStats = $this->calculateTasbeehStats($user, $tasbeeh, $progress->fresh());

        return [
            'success' => true,
            'added_count' => $countToAdd,
            'message' => "+{$countToAdd} completed for '{$tasbeeh->title}'!",
            'stats' => $updatedStats,
        ];
    }

    /**
     * Marks all active Tasbeehs as completed for today for the user by adding each tasbeeh's Daily Target.
     */
    public function completeAllForToday(User $user): array
    {
        $activeTasbeehs = Tasbeeh::query()->active()->get();
        $totalAdded = 0;
        $completedCount = 0;

        DB::transaction(function () use ($user, $activeTasbeehs, &$totalAdded, &$completedCount) {
            $lifetime = $this->getOrCreateLifetimeRecord($user);
            $today = $this->now()->format('Y-m-d');

            foreach ($activeTasbeehs as $tasbeeh) {
                $progress = $this->getOrCreateProgress($user, $tasbeeh);
                $countToAdd = max((int) $tasbeeh->daily_target, 1);

                $progress->increment('total_completed', $countToAdd);
                $progress->update(['last_zikr_at' => $this->now()]);

                if ($lifetime->exists) {
                    $lifetime->increment('lifetime_count', $countToAdd);
                    $lifetime->update(['last_zikr_at' => $this->now()]);
                }

                if (Schema::hasTable('user_daily_zikrs')) {
                    $daily = UserDailyZikr::firstOrCreate(
                        ['user_id' => $user->id, 'tasbeeh_id' => $tasbeeh->id, 'date' => $today],
                        ['count' => 0]
                    );
                    $daily->increment('count', $countToAdd);
                }

                $totalAdded += $countToAdd;
                $completedCount++;
            }
        });

        if ($totalAdded === 0) {
            return [
                'success' => false,
                'already_completed' => true,
                'message' => 'No active tasbeehs found.',
            ];
        }

        return [
            'success' => true,
            'message' => "Today's daily quota added across {$completedCount} active tasbeeh(s) (+{$totalAdded} total)!",
        ];
    }

    /**
     * Resets ALL Tasbeeh tracking progress for the user to 0 and sets start date to today.
     * NOTE: Lifetime Total Zikr is NEVER touched by this reset!
     */
    public function resetAllProgress(User $user): array
    {
        $activeTasbeehs = Tasbeeh::query()->active()->get();
        $today = $this->now()->format('Y-m-d');

        DB::transaction(function () use ($user, $activeTasbeehs, $today) {
            foreach ($activeTasbeehs as $tasbeeh) {
                $progress = $this->getOrCreateProgress($user, $tasbeeh);
                $progress->update([
                    'total_completed' => 0,
                    'tracking_start_date' => $today,
                    'last_zikr_at' => null,
                ]);
            }

            if (Schema::hasTable('user_daily_zikrs')) {
                UserDailyZikr::where('user_id', $user->id)
                    ->where('date', $today)
                    ->delete();
            }
        });

        return [
            'success' => true,
            'message' => 'All tasbeehs have been reset to 0 with start date set to today.',
        ];
    }

    /**
     * Dedicated reset for the Lifetime Total Zikr counter and duration only.
     */
    public function resetLifetimeZikr(User $user): array
    {
        $lifetime = $this->getOrCreateLifetimeRecord($user);
        $now = $this->now();
        if ($lifetime->exists) {
            $lifetime->update([
                'lifetime_count' => 0,
                'started_at' => $now,
                'last_zikr_at' => null,
            ]);
        }

        $duration = $this->formatDurationParts($now, $now);

        return [
            'success' => true,
            'message' => 'Lifetime Total Zikr counter and tracking duration have been reset to 0.',
            'lifetime_count' => 0,
            'lifetime_duration' => $duration,
        ];
    }
}

