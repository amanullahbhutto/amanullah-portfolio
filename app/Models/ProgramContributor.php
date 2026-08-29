<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ProgramContributor extends Model
{
    protected $fillable = ['program_id', 'name', 'father_name', 'from_location', 'city_id'];
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function city(): BelongsTo { return $this->belongsTo(City::class); }
    public function contributions(): HasMany { return $this->hasMany(ProgramContribution::class, 'contributor_id'); }
    public function getTotalGivenAttribute(): float { return (float) $this->contributions()->sum('amount'); }
    public function getLocationDisplayAttribute(): string { return $this->city?->name ?: ($this->from_location ?: '-'); }
}
