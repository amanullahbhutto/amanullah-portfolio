<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDailyZikr extends Model
{
    use HasFactory;

    protected $table = 'user_daily_zikrs';

    protected $fillable = [
        'user_id',
        'tasbeeh_id',
        'date',
        'count',
    ];

    protected $casts = [
        'date' => 'string',
        'count' => 'integer',
    ];

    public function setDateAttribute($value): void
    {
        $this->attributes['date'] = $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : substr((string) $value, 0, 10);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasbeeh(): BelongsTo
    {
        return $this->belongsTo(Tasbeeh::class);
    }
}
