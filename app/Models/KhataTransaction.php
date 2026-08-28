<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KhataTransaction extends Model
{
    use HasFactory;

    public const TYPE_PESE_LIYE = 'pese_liye'; // Payment received from customer (Credit into khata)
    public const TYPE_PESE_DIYE = 'pese_diye'; // Payment given to customer (Debit into khata)

    protected $fillable = [
        'khata_customer_id',
        'type',
        'amount',
        'transaction_date',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(KhataCustomer::class, 'khata_customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_PESE_LIYE => 'Pese Liye (Received)',
            self::TYPE_PESE_DIYE => 'Pese Diye (Given)',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}

