<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\InvestmentWithdrawal;
use App\Models\Investor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
class InvestmentWithdrawalController extends Controller
{
    public function __construct(){ $this->middleware('permission:view investment withdrawals')->only('index'); $this->middleware('permission:create investment withdrawal')->only('store'); }
    public function index(Request $request): View
    {
        $investorId=$request->integer('investor_id'); $from=$request->string('date_from')->toString(); $to=$request->string('date_to')->toString(); $q=trim($request->string('q')->toString());
        $base=InvestmentWithdrawal::query()->with('investor')
            ->when($investorId,fn($x)=>$x->where('investor_id',$investorId))
            ->when($from,fn($x)=>$x->whereDate('withdrawal_date','>=',$from))
            ->when($to,fn($x)=>$x->whereDate('withdrawal_date','<=',$to))
            ->when($q!=='',fn($x)=>$x->where(fn($s)=>$s->where('payment_method','like',"%{$q}%")->orWhere('reference_number','like',"%{$q}%")->orWhere('notes','like',"%{$q}%")->orWhereHas('investor',fn($inv)=>$inv->where('name','like',"%{$q}%"))));
        $total=(float)(clone $base)->sum('amount');
        $withdrawals=$base->orderByDesc('withdrawal_date')->orderByDesc('id')->paginate(20)->withQueryString();
        $investors=Investor::withSum('investments as total_investment_sum','amount')->withSum('withdrawals as total_withdrawn_sum','amount')->latest()->get();
        return view('admin.investment-withdrawals.index',compact('withdrawals','investors','investorId','total','from','to','q'));
    }
    public function store(Request $request): JsonResponse
    {
        $data=$request->validate(['investor_id'=>['required','exists:investors,id'],'amount'=>['required','numeric','gt:0'],'withdrawal_date'=>['required','date'],'payment_method'=>['nullable','string','max:50'],'reference_number'=>['nullable','string','max:120'],'notes'=>['nullable','string','max:3000']]);
        $investor=Investor::findOrFail($data['investor_id']);
        if((float)$data['amount']>$investor->current_investment+0.001) throw ValidationException::withMessages(['amount'=>'Withdrawal cannot exceed current investment of Rs. '.number_format($investor->current_investment,2).'.']);
        InvestmentWithdrawal::create([...$data,'created_by'=>auth()->id()]); return response()->json(['message'=>'Investment withdrawal recorded successfully.'],201);
    }
}
