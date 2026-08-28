<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tasbeeh extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'arabic_text',
        'urdu_meaning',
        'daily_target',
        'sort_order',
        'is_active',
        'description',
        'transliteration',
        'reference',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'daily_target' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(UserTasbeehProgress::class);
    }

    public function userProgress(User $user): HasOne
    {
        return $this->hasOne(UserTasbeehProgress::class)->where('user_id', $user->id);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }
}

