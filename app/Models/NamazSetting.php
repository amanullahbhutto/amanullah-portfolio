<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NamazSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'fajr_time',
        'zuhr_time',
        'asr_time',
        'maghrib_time',
        'isha_time',
        'jummah_time',
    ];

    public static function getSettings(): self
    {
        return static::query()->firstOrCreate([], [
            'fajr_time' => '05:00',
            'zuhr_time' => '13:15',
            'asr_time' => '16:45',
            'maghrib_time' => '18:50',
            'isha_time' => '20:15',
            'jummah_time' => '13:30',
        ]);
    }

    public function getTimeForPrayer(string $prayerKey, CarbonInterface|string $date): string
    {
        $carbonDate = $date instanceof CarbonInterface ? $date : Carbon::parse($date);

        if ($prayerKey === 'zuhr' && $carbonDate->isFriday()) {
            return $this->jummah_time ?: '13:30';
        }

        return match ($prayerKey) {
            'fajr' => $this->fajr_time ?: '05:00',
            'zuhr' => $this->zuhr_time ?: '13:15',
            'asr' => $this->asr_time ?: '16:45',
            'maghrib' => $this->maghrib_time ?: '18:50',
            'isha' => $this->isha_time ?: '20:15',
            default => '12:00',
        };
    }
}

