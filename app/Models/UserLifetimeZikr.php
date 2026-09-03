<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLifetimeZikr extends Model
{
    use HasFactory;

    protected $table = 'user_lifetime_zikrs';

    protected $fillable = [
        'user_id',
        'lifetime_count',
        'started_at',
        'last_zikr_at',
    ];

    protected function casts(): array
    {
        return [
            'lifetime_count' => 'integer',
            'started_at' => 'datetime',
            'last_zikr_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

