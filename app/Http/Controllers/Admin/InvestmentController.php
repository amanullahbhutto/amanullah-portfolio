<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\Investor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class InvestmentController extends Controller
{
    public function __construct(){ $this->middleware('permission:view investments')->only('index'); $this->middleware('permission:create investment')->only('store'); $this->middleware('permission:update investment')->only('update'); $this->middleware('permission:delete investment')->only('destroy'); }
    public function index(Request $request): View
    {
        $investorId=$request->integer('investor_id'); $from=$request->string('date_from')->toString(); $to=$request->string('date_to')->toString(); $q=trim($request->string('q')->toString());
        $base=Investment::query()->with('investor')
            ->when($investorId,fn($x)=>$x->where('investor_id',$investorId))
            ->when($from,fn($x)=>$x->whereDate('investment_date','>=',$from))
            ->when($to,fn($x)=>$x->whereDate('investment_date','<=',$to))
            ->when($q!=='',fn($x)=>$x->where(fn($s)=>$s->where('payment_method','like',"%{$q}%")->orWhere('reference_number','like',"%{$q}%")->orWhere('notes','like',"%{$q}%")->orWhereHas('investor',fn($inv)=>$inv->where('name','like',"%{$q}%"))));
        $total=(float)(clone $base)->sum('amount');
        $investments=$base->orderByDesc('investment_date')->orderByDesc('id')->paginate(20)->withQueryString();
        $investors=Investor::latest()->get();
        return view('admin.investments.index',compact('investments','investors','investorId','total','from','to','q'));
    }
    public function store(Request $request): JsonResponse { Investment::create($this->validated($request)); return response()->json(['message'=>'Investment added successfully.'],201); }
    public function update(Request $request, Investment $investment): JsonResponse { $investment->update($this->validated($request)); return response()->json(['message'=>'Investment updated successfully.']); }
    public function destroy(Investment $investment): JsonResponse { $investment->delete(); return response()->json(['message'=>'Investment deleted successfully.']); }
    private function validated(Request $request): array { return $request->validate(['investor_id'=>['required','exists:investors,id'],'amount'=>['required','numeric','gt:0'],'investment_date'=>['required','date'],'payment_method'=>['nullable','string','max:50'],'reference_number'=>['nullable','string','max:120'],'notes'=>['nullable','string','max:3000']]); }
}
