<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NamazAttendance extends Model
{
    use HasFactory;

    public const STATUS_JAMAT = 'jamat';
    public const STATUS_WITHOUT_JAMAT = 'without_jamat';
    public const STATUS_KAZA = 'kaza';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_PENDING = 'pending';

    public const PRAYERS = ['fajr', 'zuhr', 'asr', 'maghrib', 'isha'];

    protected $fillable = [
        'user_id',
        'attendance_date',
        'fajr_status',
        'zuhr_status',
        'asr_status',
        'maghrib_status',
        'isha_status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

