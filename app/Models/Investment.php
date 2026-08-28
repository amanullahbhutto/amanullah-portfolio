<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Investment extends Model
{
    protected $fillable = ['investor_id','amount','investment_date','payment_method','reference_number','notes'];
    protected function casts(): array { return ['investment_date'=>'date','amount'=>'decimal:2']; }
    public function investor(): BelongsTo { return $this->belongsTo(Investor::class); }
}
