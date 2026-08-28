<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Investor;
use Illuminate\Support\Collection;
use Illuminate\View\View;
class InvestorLedgerController extends Controller
{
    public function __construct(){ $this->middleware('permission:view investors'); }
    public function __invoke(Investor $investor): View
    {
        $investor->load(['investments','withdrawals','allocations.period','profitPayments']);
        $rows=collect();
        foreach($investor->investments as $x){ $rows->push(['id'=>$x->id,'date'=>$x->investment_date,'type'=>'Investment','description'=>$x->reference_number ?: 'Investment added','credit'=>(float)$x->amount,'debit'=>0]); }
        foreach($investor->withdrawals as $x){ $rows->push(['id'=>$x->id,'date'=>$x->withdrawal_date,'type'=>'Investment Withdrawal','description'=>$x->reference_number ?: 'Investment withdrawn','credit'=>0,'debit'=>(float)$x->amount]); }
        foreach($investor->allocations as $x){ $rows->push(['id'=>$x->id,'date'=>$x->period->date_to,'type'=>'Profit Generated','description'=>$x->period->date_from->format('M d, Y').' - '.$x->period->date_to->format('M d, Y'),'credit'=>(float)$x->profit_amount,'debit'=>0]); }
        foreach($investor->profitPayments as $x){ $rows->push(['id'=>$x->id,'date'=>$x->payment_date,'type'=>'Profit Payment','description'=>$x->reference_number ?: 'Profit paid','credit'=>0,'debit'=>(float)$x->amount]); }
        $running=0.0;
        $ledger=$rows->sort(function($a, $b) {
            $cmp = strcmp($a['date']->format('Y-m-d'), $b['date']->format('Y-m-d'));
            return $cmp !== 0 ? $cmp : ($a['id'] <=> $b['id']);
        })->values()->map(function($row) use (&$running){ $running += $row['credit']-$row['debit']; $row['balance']=$running; return $row; })->reverse()->values();
        return view('admin.investor-ledger.index',compact('investor','ledger'));
    }
}
