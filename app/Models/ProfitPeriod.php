<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ProfitPeriod extends Model
{
    protected $fillable = ['date_from','date_to','total_sales','product_cost','business_expenses','net_profit','total_investor_profit','owner_profit','created_by','confirmed_at'];
    protected function casts(): array { return ['date_from'=>'date','date_to'=>'date','confirmed_at'=>'datetime','total_sales'=>'decimal:2','product_cost'=>'decimal:2','business_expenses'=>'decimal:2','net_profit'=>'decimal:2','total_investor_profit'=>'decimal:2','owner_profit'=>'decimal:2']; }
    public function allocations(): HasMany { return $this->hasMany(ProfitAllocation::class); }
}
