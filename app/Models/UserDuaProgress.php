<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDuaProgress extends Model
{
    use HasFactory;

    protected $table = 'user_dua_progress';

    protected $fillable = [
        'user_id',
        'dua_id',
        'total_completed',
        'tracking_start_date',
        'last_dua_at',
    ];

    protected function casts(): array
    {
        return [
            'total_completed' => 'integer',
            'tracking_start_date' => 'date',
            'last_dua_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dua(): BelongsTo
    {
        return $this->belongsTo(Dua::class);
    }
}

