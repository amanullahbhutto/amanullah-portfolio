<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTasbeehProgress extends Model
{
    use HasFactory;

    protected $table = 'user_tasbeeh_progress';

    protected $fillable = [
        'user_id',
        'tasbeeh_id',
        'total_completed',
        'tracking_start_date',
        'last_zikr_at',
    ];

    protected function casts(): array
    {
        return [
            'total_completed' => 'integer',
            'tracking_start_date' => 'date',
            'last_zikr_at' => 'datetime',
        ];
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

