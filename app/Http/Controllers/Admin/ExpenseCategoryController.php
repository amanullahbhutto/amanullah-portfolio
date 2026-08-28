<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
class ExpenseCategoryController extends Controller
{
    public function __construct(){ $this->middleware('permission:view expense categories')->only('index'); $this->middleware('permission:create expense category')->only('store'); $this->middleware('permission:update expense category')->only('update'); $this->middleware('permission:delete expense category')->only('destroy'); }
    public function index(Request $request): View
    {
        $q=trim($request->string('q')->toString()); $status=trim($request->string('status')->toString());
        $categories=ExpenseCategory::withSum('expenses as total_expenses_sum','amount')->when($q!=='',fn($query)=>$query->where('name','like',"%{$q}%"))->when($status!=='',fn($query)=>$query->where('status',$status))->latest()->paginate(20)->withQueryString();
        return view('admin.expense-categories.index',compact('categories','status'));
    }
    public function store(Request $request): JsonResponse { $category=ExpenseCategory::create($this->validated($request)); return response()->json(['message'=>'Expense category added successfully.','category'=>['id'=>$category->id,'name'=>$category->name]],201); }
    public function update(Request $request, ExpenseCategory $expenseCategory): JsonResponse { $expenseCategory->update($this->validated($request,$expenseCategory->id)); return response()->json(['message'=>'Expense category updated successfully.','category'=>['id'=>$expenseCategory->id,'name'=>$expenseCategory->name]]); }
    public function destroy(ExpenseCategory $expenseCategory): JsonResponse
    {
        if($expenseCategory->expenses()->exists()) throw ValidationException::withMessages(['category'=>'This category is already used in expense transactions and cannot be deleted. You can make it inactive instead.']);
        $expenseCategory->delete(); return response()->json(['message'=>'Expense category deleted successfully.']);
    }
    private function validated(Request $request,?int $id=null): array { return $request->validate(['name'=>['required','string','max:120',Rule::unique('expense_categories','name')->ignore($id)],'description'=>['nullable','string','max:3000'],'status'=>['required',Rule::in(['active','inactive'])]]); }
}
