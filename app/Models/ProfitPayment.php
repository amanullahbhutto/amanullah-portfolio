<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProfitPayment extends Model
{
    protected $fillable = ['investor_id','amount','payment_date','payment_method','reference_number','notes','created_by'];
    protected function casts(): array { return ['payment_date'=>'date','amount'=>'decimal:2']; }
    public function investor(): BelongsTo { return $this->belongsTo(Investor::class); }
}
