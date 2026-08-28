<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Experience extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'bullets' => 'array',
        ];
    }

    public static function totalDurationMonths(?Collection $experiences = null): int
    {
        $experiences ??= self::query()->get(['start_date', 'end_date', 'is_current']);

        return (int) $experiences->sum(function (self $experience): int {
            return $experience->durationMonths();
        });
    }

    public static function formattedTotalDuration(?Collection $experiences = null): string
    {
        return self::formatMonths(self::totalDurationMonths($experiences));
    }

    public function durationMonths(): int
    {
        if (! $this->start_date) {
            return 0;
        }

        $startDate = $this->start_date->copy()->startOfMonth();
        $endDate = ($this->is_current || $this->end_date === null ? now() : $this->end_date)
            ->copy()
            ->startOfMonth();

        if ($endDate->lessThan($startDate)) {
            return 0;
        }

        return (int) $startDate->diffInMonths($endDate);
    }

    public function getFormattedDurationAttribute(): string
    {
        return self::formatMonths($this->durationMonths());
    }

    private static function formatMonths(int $months): string
    {
        $years = intdiv($months, 12);
        $remainingMonths = $months % 12;

        return $years.'y '.$remainingMonths.'m';
    }
}
