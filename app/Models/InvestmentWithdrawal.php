<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class InvestmentWithdrawal extends Model
{
    protected $fillable = ['investor_id','amount','withdrawal_date','payment_method','reference_number','notes','created_by'];
    protected function casts(): array { return ['withdrawal_date'=>'date','amount'=>'decimal:2']; }
    public function investor(): BelongsTo { return $this->belongsTo(Investor::class); }
}
