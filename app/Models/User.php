<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'namaz_start_date',
        'zikr_arabic_size',
        'zikr_urdu_size',
        'zikr_show_arabic',
        'zikr_show_urdu',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'namaz_start_date' => 'date',
            'zikr_arabic_size' => 'integer',
            'zikr_urdu_size' => 'integer',
            'zikr_show_arabic' => 'boolean',
            'zikr_show_urdu' => 'boolean',
        ];
    }

    public function namazAttendances(): HasMany
    {
        return $this->hasMany(NamazAttendance::class);
    }

    public function tasbeehProgress(): HasMany
    {
        return $this->hasMany(UserTasbeehProgress::class);
    }

    public function scopeMuslim(Builder $query): Builder
    {
        return $query->whereHas('roles', fn ($q) => $q->where('name', 'Muslim'));
    }

    public function isMuslim(): bool
    {
        return $this->hasRole('Muslim');
    }
}
