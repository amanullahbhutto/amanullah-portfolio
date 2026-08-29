<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'name',
        'state',
        'country',
        'status',
    ];

    /**
     * Scope a query to only include active cities.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Contributors associated with this city.
     */
    public function contributors(): HasMany
    {
        return $this->hasMany(ProgramContributor::class);
    }
}

