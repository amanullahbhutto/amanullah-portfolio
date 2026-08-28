<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KhataCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'opening_balance',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(KhataTransaction::class, 'khata_customer_id');
    }

    public function getTotalPeseLiyeAttribute(): float
    {
        if (array_key_exists('total_pese_liye_sum', $this->attributes)) {
            return (float) ($this->attributes['total_pese_liye_sum'] ?? 0);
        }

        return (float) $this->transactions()->where('type', KhataTransaction::TYPE_PESE_LIYE)->sum('amount');
    }

    public function getTotalPeseDiyeAttribute(): float
    {
        if (array_key_exists('total_pese_diye_sum', $this->attributes)) {
            return (float) ($this->attributes['total_pese_diye_sum'] ?? 0);
        }

        return (float) $this->transactions()->where('type', KhataTransaction::TYPE_PESE_DIYE)->sum('amount');
    }

    public function getCurrentBalanceAttribute(): float
    {
        return (float) $this->opening_balance + $this->total_pese_diye - $this->total_pese_liye;
    }

    public function getBalanceStatusAttribute(): string
    {
        $balance = $this->current_balance;
        if ($balance > 0) {
            return 'receivable'; // Lena hai (Customer owes money)
        }
        if ($balance < 0) {
            return 'payable'; // Dena hai (Advance/overpayment)
        }
        return 'settled';
    }
}

