<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PwaSyncLog extends Model
{
    use HasFactory;

    protected $table = 'pwa_sync_logs';

    protected $fillable = [
        'user_id',
        'operation_uuid',
        'idempotency_key',
        'entity',
        'action',
        'status',
        'payload',
        'server_id',
        'client_temp_id',
        'error_message',
        'retry_count',
    ];

    protected $casts = [
        'payload' => 'array',
        'retry_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

