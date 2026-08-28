<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProgramContribution extends Model
{
    protected $fillable = ['program_id','contributor_id','amount','contribution_date','details','reference','notes','created_by'];
    protected function casts(): array { return ['contribution_date'=>'date','amount'=>'decimal:2']; }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function contributor(): BelongsTo { return $this->belongsTo(ProgramContributor::class,'contributor_id'); }
}
