<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = ['name', 'program_date', 'location', 'city_id', 'description', 'status', 'notes'];

    protected function casts(): array
    {
        return ['program_date' => 'date'];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function contributors(): HasMany
    {
        return $this->hasMany(ProgramContributor::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(ProgramContribution::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ProgramExpense::class);
    }

    public function getTotalReceivedAttribute(): float
    {
        return (float) ($this->attributes['total_received_sum'] ?? $this->contributions()->sum('amount'));
    }

    public function getTotalExpensesAttribute(): float
    {
        return (float) ($this->attributes['total_expenses_sum'] ?? $this->expenses()->sum('amount'));
    }

    public function getRemainingBalanceAttribute(): float
    {
        return $this->total_received - $this->total_expenses;
    }

    public function getLocationDisplayAttribute(): string
    {
        return $this->city?->name ?: ($this->location ?: '-');
    }
}
