<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramContribution;
use App\Models\ProgramExpense;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
class ProgramTransactionController extends Controller
{
    public function __construct(){ $this->middleware('permission:view program transactions'); }
    public function index(Request $request): View
    {
        $programId=$request->integer('program_id'); $type=$request->string('type','all')->toString(); $from=$request->string('date_from')->toString(); $to=$request->string('date_to')->toString(); $name=trim($request->string('q')->toString());
        $rows=collect();
        if(in_array($type,['all','contribution'],true)){
            $items=ProgramContribution::with(['program','contributor'])->when($programId,fn($q)=>$q->where('program_id',$programId))->when($from,fn($q)=>$q->whereDate('contribution_date','>=',$from))->when($to,fn($q)=>$q->whereDate('contribution_date','<=',$to))->when($name!=='',fn($q)=>$q->whereHas('contributor',fn($s)=>$s->where('name','like',"%{$name}%")))->latest('contribution_date')->orderByDesc('id')->get();
            $rows=$rows->concat($items->map(fn($x)=>['id'=>$x->id,'date'=>$x->contribution_date,'program'=>$x->program->name,'type'=>'Income','name'=>$x->contributor->name,'father_name'=>$x->contributor->father_name,'from'=>$x->contributor->from_location,'details'=>$x->details,'money_in'=>(float)$x->amount,'money_out'=>0]));
        }
        if(in_array($type,['all','expense'],true)){
            $items=ProgramExpense::with(['program','category'])->when($programId,fn($q)=>$q->where('program_id',$programId))->when($from,fn($q)=>$q->whereDate('expense_date','>=',$from))->when($to,fn($q)=>$q->whereDate('expense_date','<=',$to))->when($name!=='',fn($q)=>$q->where('paid_to','like',"%{$name}%"))->latest('expense_date')->orderByDesc('id')->get();
            $rows=$rows->concat($items->map(fn($x)=>['id'=>$x->id,'date'=>$x->expense_date,'program'=>$x->program->name,'type'=>'Expense','name'=>$x->paid_to ?: $x->category->name,'father_name'=>null,'from'=>null,'details'=>trim($x->category->name.($x->details?' - '.$x->details:'')),'money_in'=>0,'money_out'=>(float)$x->amount]));
        }
        $rows=$rows->sort(function($a, $b) {
            $cmp = strcmp($b['date']->format('Y-m-d'), $a['date']->format('Y-m-d'));
            return $cmp !== 0 ? $cmp : ($b['id'] <=> $a['id']);
        })->values();
        $totalReceived=(float)$rows->sum('money_in'); $totalExpenses=(float)$rows->sum('money_out');
        $page=LengthAwarePaginator::resolveCurrentPage(); $perPage=20; $transactions=new LengthAwarePaginator($rows->forPage($page,$perPage)->values(),$rows->count(),$perPage,$page,['path'=>$request->url(),'query'=>$request->query()]);
        $programs=Program::latest()->get(); return view('admin.program-transactions.index',compact('transactions','programs','programId','type','totalReceived','totalExpenses'));
    }
}
