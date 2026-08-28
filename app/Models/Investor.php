<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investor extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'cnic_reference', 'profit_share_percentage', 'joining_date', 'status', 'notes'];

    protected function casts(): array
    {
        return ['joining_date' => 'date', 'profit_share_percentage' => 'decimal:2'];
    }

    public function investments(): HasMany { return $this->hasMany(Investment::class); }
    public function allocations(): HasMany { return $this->hasMany(ProfitAllocation::class); }
    public function profitPayments(): HasMany { return $this->hasMany(ProfitPayment::class); }
    public function withdrawals(): HasMany { return $this->hasMany(InvestmentWithdrawal::class); }

    public function getTotalInvestmentAttribute(): float { return (float) ($this->attributes['total_investment_sum'] ?? $this->investments()->sum('amount')); }
    public function getTotalWithdrawnAttribute(): float { return (float) ($this->attributes['total_withdrawn_sum'] ?? $this->withdrawals()->sum('amount')); }
    public function getCurrentInvestmentAttribute(): float { return $this->total_investment - $this->total_withdrawn; }
    public function getTotalProfitAttribute(): float { return (float) ($this->attributes['total_profit_sum'] ?? $this->allocations()->sum('profit_amount')); }
    public function getPaidProfitAttribute(): float { return (float) ($this->attributes['paid_profit_sum'] ?? $this->profitPayments()->sum('amount')); }
    public function getPendingProfitAttribute(): float { return max(0, $this->total_profit - $this->paid_profit); }
}
