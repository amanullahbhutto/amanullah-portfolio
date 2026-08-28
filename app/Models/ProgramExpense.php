<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProgramExpense extends Model
{
    protected $fillable = ['program_id','expense_category_id','expense_date','amount','paid_to','details','reference','notes','created_by'];
    protected function casts(): array { return ['expense_date'=>'date','amount'=>'decimal:2']; }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function category(): BelongsTo { return $this->belongsTo(ExpenseCategory::class,'expense_category_id'); }
}
