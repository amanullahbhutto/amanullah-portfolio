<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Investor;
use App\Models\ProfitAllocation;
use App\Models\ProfitPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
class ProfitSharingController extends Controller
{
    public function __construct(){ $this->middleware('permission:view profit sharing')->only(['index','preview']); $this->middleware('permission:confirm profit sharing')->only('store'); }
    public function index(): View
    {
        $periods=ProfitPeriod::with(['allocations.investor'])->orderByDesc('id')->paginate(15);
        return view('admin.profit-sharing.index',compact('periods'));
    }
    public function preview(Request $request): JsonResponse
    {
        $data=$this->validated($request); return response()->json($this->calculate($data));
    }
    public function store(Request $request): JsonResponse
    {
        $data=$this->validated($request); $calc=$this->calculate($data);
        if (ProfitPeriod::where('date_from',$data['date_from'])->where('date_to',$data['date_to'])->exists()) throw ValidationException::withMessages(['date_from'=>'A confirmed profit period already exists for this exact date range.']);
        DB::transaction(function() use($data,$calc): void {
            $period=ProfitPeriod::create([...$data,'net_profit'=>$calc['net_profit'],'total_investor_profit'=>$calc['total_investor_profit'],'owner_profit'=>$calc['owner_profit'],'created_by'=>auth()->id(),'confirmed_at'=>now()]);
            foreach($calc['allocations'] as $row){ ProfitAllocation::create(['profit_period_id'=>$period->id,'investor_id'=>$row['id'],'profit_percentage'=>$row['percentage'],'profit_amount'=>$row['amount']]); }
        });
        return response()->json(['message'=>'Profit sharing confirmed and investor ledgers updated.'],201);
    }
    private function validated(Request $request): array { return $request->validate(['date_from'=>['required','date'],'date_to'=>['required','date','after_or_equal:date_from'],'total_sales'=>['required','numeric','min:0'],'product_cost'=>['required','numeric','min:0'],'business_expenses'=>['required','numeric','min:0']]); }
    private function calculate(array $data): array
    {
        $investors=Investor::where('status','active')->latest()->get(); $pct=(float)$investors->sum('profit_share_percentage');
        if($pct>100.0001) throw ValidationException::withMessages(['total_sales'=>"Active investor profit percentages total {$pct}%. They cannot exceed 100%."]);
        $net=(float)$data['total_sales']-(float)$data['product_cost']-(float)$data['business_expenses'];
        $alloc=[]; $total=0.0;
        foreach($investors as $investor){ $amount=$net>0?round($net*((float)$investor->profit_share_percentage/100),2):0; $total+=$amount; $alloc[]=['id'=>$investor->id,'name'=>$investor->name,'percentage'=>(float)$investor->profit_share_percentage,'amount'=>$amount]; }
        return ['net_profit'=>round($net,2),'total_investor_profit'=>round($total,2),'owner_profit'=>round($net-$total,2),'total_percentage'=>$pct,'allocations'=>$alloc];
    }
}
