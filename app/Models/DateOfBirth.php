<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DateOfBirth extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'father_name',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Age / Duration
    |--------------------------------------------------------------------------
    |
    | If end_date exists:
    | start_date -> end_date
    |
    | Otherwise:
    | start_date -> today
    |
    */
    public function getAgeAttribute(): array
    {
        $startDate = Carbon::parse($this->start_date)->startOfDay();

        $endDate = $this->end_date
            ? Carbon::parse($this->end_date)->startOfDay()
            : now()->startOfDay();

        $difference = $startDate->diff($endDate);

        return [
            'years' => $difference->y,
            'months' => $difference->m,
            'days' => $difference->d,
        ];
    }

    public function getFormattedAgeAttribute(): string
    {
        $age = $this->age;

        return "{$age['years']} Years, {$age['months']} Months, {$age['days']} Days";
    }

    public function getNextBirthdayAttribute(): Carbon
    {
        $today = now()->startOfDay();
        $nextBirthday = $this->birthdayForYear($today->year);

        if ($nextBirthday->lessThan($today)) {
            $nextBirthday = $this->birthdayForYear($today->year + 1);
        }

        return $nextBirthday;
    }

    public function getDaysUntilNextBirthdayAttribute(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->next_birthday);
    }

    public function getNextBirthdayCountdownAttribute(): array
    {
        $difference = now()->startOfDay()->diff($this->next_birthday);

        return [
            'months' => ($difference->y * 12) + $difference->m,
            'days' => $difference->d,
        ];
    }

    public function getFormattedNextBirthdayCountdownAttribute(): string
    {
        $countdown = $this->next_birthday_countdown;

        return "{$countdown['months']} Months, {$countdown['days']} Days";
    }

    private function birthdayForYear(int $year): Carbon
    {
        $dateOfBirth = Carbon::parse($this->start_date);
        $month = $dateOfBirth->month;
        $day = $dateOfBirth->day;

        if (! checkdate($month, $day, $year)) {
            $day = Carbon::create($year, $month, 1)->endOfMonth()->day;
        }

        return Carbon::create($year, $month, $day)->startOfDay();
    }
}
