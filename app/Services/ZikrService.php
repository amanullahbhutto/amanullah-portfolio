<?php

namespace App\Services;

use App\Models\Tasbeeh;
use App\Models\User;
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
     * Gets or initializes the persistent lifetime/all-time Zikr record for a user.
     * This record is completely independent and never reset by standard cycle resets or tasbeeh deletions.
     */
    public function getOrCreateLifetimeRecord(User $user): UserLifetimeZikr
    {
        if (!Schema::hasTable('user_lifetime_zikrs')) {
            $dummy = new UserLifetimeZikr();
            $dummy->user_id = $user->id;
            $dummy->lifetime_count = 0;
            $dummy->last_zikr_at = null;
            return $dummy;
        }

        return UserLifetimeZikr::firstOrCreate(
            ['user_id' => $user->id],
            [
                'lifetime_count' => 0,
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
        ?UserTasbeehProgress $progress = null
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

        // Calculate completed portion for today's quota
        $priorRequired = max($activeDays - 1, 0) * $dailyTarget;
        $todayCompleted = max($totalCompleted - $priorRequired, 0);
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

        $tasbeehStats = [];
        $overallTodayRequired = 0;
        $overallTodayCompleted = 0;
        $overallTotalRequired = 0;
        $overallTotalCompleted = 0;
        $overallBacklog = 0;
        $overallExtra = 0;

        foreach ($activeTasbeehs as $tasbeeh) {
            $progress = $progressMap->get($tasbeeh->id);
            $stats = $this->calculateTasbeehStats($user, $tasbeeh, $progress);
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

        $overallPercentage = $overallTotalRequired > 0
            ? min(round(($overallTotalCompleted / $overallTotalRequired) * 100, 1), 100)
            : 100;

        $lifetimeRecord = $this->getOrCreateLifetimeRecord($user);

        return [
            'user' => $user,
            'lifetime_total' => (int) $lifetimeRecord->lifetime_count,
            'lifetime_last_zikr' => $lifetimeRecord->last_zikr_at ? Carbon::parse($lifetimeRecord->last_zikr_at, $this->getTimezone())->diffForHumans() : null,
            'total_active_tasbeehs' => $activeTasbeehs->count(),
            'overall_today_required' => $overallTodayRequired,
            'overall_today_completed' => $overallTodayCompleted,
            'overall_today_percentage' => $overallTodayPercentage,
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
     * Also seamlessly updates the persistent lifetime/all-time counter.
     */
    public function addCount(User $user, Tasbeeh $tasbeeh, int $count, string $source = 'live'): array
    {
        if ($count === 0) {
            throw new \InvalidArgumentException('Zikr count cannot be zero.');
        }

        $progress = DB::transaction(function () use ($user, $tasbeeh, $count) {
            $record = $this->getOrCreateProgress($user, $tasbeeh);
            $lifetime = $this->getOrCreateLifetimeRecord($user);

            if ($count > 0) {
                // Atomic database increment for both active cycle and lifetime
                $record->increment('total_completed', $count);
                $record->update(['last_zikr_at' => $this->now()]);

                if ($lifetime->exists) {
                    $lifetime->increment('lifetime_count', $count);
                    $lifetime->update(['last_zikr_at' => $this->now()]);
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
     * Marks a single Tasbeeh as completed for today for the user.
     * Increments the persistent lifetime counter by the difference.
     */
    public function completeSingleForToday(User $user, Tasbeeh $tasbeeh): array
    {
        $progress = $this->getOrCreateProgress($user, $tasbeeh);
        $stats = $this->calculateTasbeehStats($user, $tasbeeh, $progress);

        if ($stats['remaining'] > 0) {
            $difference = $stats['remaining'];
            DB::transaction(function () use ($user, $progress, $stats, $difference) {
                $progress->update([
                    'total_completed' => $stats['total_required'],
                    'last_zikr_at' => $this->now(),
                ]);

                $lifetime = $this->getOrCreateLifetimeRecord($user);
                if ($lifetime->exists) {
                    $lifetime->increment('lifetime_count', $difference);
                    $lifetime->update(['last_zikr_at' => $this->now()]);
                }
            });
        }

        $updatedStats = $this->calculateTasbeehStats($user, $tasbeeh, $progress->fresh());

        return [
            'success' => true,
            'message' => "'{$tasbeeh->title}' marked as completed for today!",
            'stats' => $updatedStats,
        ];
    }

    /**
     * Marks all active Tasbeehs as completed for today for the user.
     * Also increments the persistent lifetime counter by the completed difference.
     */
    public function completeAllForToday(User $user): array
    {
        $activeTasbeehs = Tasbeeh::query()->active()->get();
        $countCompleted = 0;

        DB::transaction(function () use ($user, $activeTasbeehs, &$countCompleted) {
            $lifetime = $this->getOrCreateLifetimeRecord($user);

            foreach ($activeTasbeehs as $tasbeeh) {
                $progress = $this->getOrCreateProgress($user, $tasbeeh);
                $stats = $this->calculateTasbeehStats($user, $tasbeeh, $progress);
                if ($stats['remaining'] > 0) {
                    $progress->update([
                        'total_completed' => $stats['total_required'],
                        'last_zikr_at' => $this->now(),
                    ]);

                    if ($lifetime->exists) {
                        $lifetime->increment('lifetime_count', $stats['remaining']);
                        $lifetime->update(['last_zikr_at' => $this->now()]);
                    }

                    $countCompleted++;
                }
            }
        });

        return [
            'success' => true,
            'message' => "All pending tasbeehs marked as completed for today!",
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
        });

        return [
            'success' => true,
            'message' => 'All tasbeehs have been reset to 0 with start date set to today.',
        ];
    }

    /**
     * Dedicated reset for the Lifetime Total Zikr counter only.
     */
    public function resetLifetimeZikr(User $user): array
    {
        $lifetime = $this->getOrCreateLifetimeRecord($user);
        if ($lifetime->exists) {
            $lifetime->update([
                'lifetime_count' => 0,
                'last_zikr_at' => null,
            ]);
        }

        return [
            'success' => true,
            'message' => 'Lifetime Total Zikr counter has been reset to 0.',
        ];
    }
}

