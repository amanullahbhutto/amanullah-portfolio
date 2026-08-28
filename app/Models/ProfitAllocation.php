<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProfitAllocation extends Model
{
    protected $fillable = ['profit_period_id','investor_id','profit_percentage','profit_amount'];
    protected function casts(): array { return ['profit_percentage'=>'decimal:2','profit_amount'=>'decimal:2']; }
    public function period(): BelongsTo { return $this->belongsTo(ProfitPeriod::class,'profit_period_id'); }
    public function investor(): BelongsTo { return $this->belongsTo(Investor::class); }
}
