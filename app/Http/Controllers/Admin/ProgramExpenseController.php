<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\Program;
use App\Models\ProgramExpense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class ProgramExpenseController extends Controller
{
    public function __construct(){ $this->middleware('permission:view program expenses')->only('index'); $this->middleware('permission:create program expense')->only('store'); $this->middleware('permission:update program expense')->only('update'); $this->middleware('permission:delete program expense')->only('destroy'); }
    public function index(Request $request): View
    {
        $programId=$request->integer('program_id'); $categoryId=$request->integer('expense_category_id'); $from=$request->string('date_from')->toString(); $to=$request->string('date_to')->toString(); $q=trim($request->string('q')->toString());
        $base=ProgramExpense::query()->when($programId,fn($x)=>$x->where('program_id',$programId))->when($categoryId,fn($x)=>$x->where('expense_category_id',$categoryId))->when($from,fn($x)=>$x->whereDate('expense_date','>=',$from))->when($to,fn($x)=>$x->whereDate('expense_date','<=',$to))->when($q!=='',fn($x)=>$x->where(fn($s)=>$s->where('paid_to','like',"%{$q}%")->orWhere('details','like',"%{$q}%")));
        $total=(float)(clone $base)->sum('amount');
        $expenses=$base->with(['program','category'])->orderByDesc('expense_date')->orderByDesc('id')->paginate(20)->withQueryString();
        $programs=Program::latest()->get(); $categories=ExpenseCategory::latest()->get();
        return view('admin.program-expenses.index',compact('expenses','programs','categories','programId','categoryId','total'));
    }
    public function store(Request $request): JsonResponse { ProgramExpense::create([...$this->validated($request),'created_by'=>auth()->id()]); return response()->json(['message'=>'Expense added successfully.'],201); }
    public function update(Request $request, ProgramExpense $expense): JsonResponse { $expense->update($this->validated($request)); return response()->json(['message'=>'Expense updated successfully.']); }
    public function destroy(ProgramExpense $expense): JsonResponse { $expense->delete(); return response()->json(['message'=>'Expense deleted successfully.']); }
    private function validated(Request $request): array { return $request->validate(['program_id'=>['required','exists:programs,id'],'expense_category_id'=>['required','exists:expense_categories,id'],'expense_date'=>['required','date'],'amount'=>['required','numeric','gt:0'],'paid_to'=>['nullable','string','max:180'],'details'=>['nullable','string','max:3000'],'reference'=>['nullable','string','max:120'],'notes'=>['nullable','string','max:3000']]); }
}
