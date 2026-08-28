<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Investor;
use App\Models\ProfitPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
class ProfitPaymentController extends Controller
{
    public function __construct(){ $this->middleware('permission:view profit payments')->only('index'); $this->middleware('permission:create profit payment')->only('store'); }
    public function index(Request $request): View
    {
        $investorId=$request->integer('investor_id'); $from=$request->string('date_from')->toString(); $to=$request->string('date_to')->toString(); $q=trim($request->string('q')->toString());
        $base=ProfitPayment::query()->with('investor')
            ->when($investorId,fn($x)=>$x->where('investor_id',$investorId))
            ->when($from,fn($x)=>$x->whereDate('payment_date','>=',$from))
            ->when($to,fn($x)=>$x->whereDate('payment_date','<=',$to))
            ->when($q!=='',fn($x)=>$x->where(fn($s)=>$s->where('payment_method','like',"%{$q}%")->orWhere('reference_number','like',"%{$q}%")->orWhere('notes','like',"%{$q}%")->orWhereHas('investor',fn($inv)=>$inv->where('name','like',"%{$q}%"))));
        $total=(float)(clone $base)->sum('amount');
        $payments=$base->orderByDesc('payment_date')->orderByDesc('id')->paginate(20)->withQueryString();
        $investors=Investor::withSum('allocations as total_profit_sum','profit_amount')->withSum('profitPayments as paid_profit_sum','amount')->latest()->get();
        return view('admin.profit-payments.index',compact('payments','investors','investorId','total','from','to','q'));
    }
    public function store(Request $request): JsonResponse
    {
        $data=$request->validate(['investor_id'=>['required','exists:investors,id'],'amount'=>['required','numeric','gt:0'],'payment_date'=>['required','date'],'payment_method'=>['nullable','string','max:50'],'reference_number'=>['nullable','string','max:120'],'notes'=>['nullable','string','max:3000']]);
        $investor=Investor::findOrFail($data['investor_id']);
        if((float)$data['amount']>$investor->pending_profit+0.001) throw ValidationException::withMessages(['amount'=>'Payment cannot be greater than the investor pending profit of Rs. '.number_format($investor->pending_profit,2).'.']);
        ProfitPayment::create([...$data,'created_by'=>auth()->id()]); return response()->json(['message'=>'Profit payment recorded successfully.'],201);
    }
}
