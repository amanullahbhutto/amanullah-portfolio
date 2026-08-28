<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\InvestmentWithdrawal;
use App\Models\Investor;
use App\Models\ProfitPayment;
use App\Models\ProfitPeriod;
use Illuminate\View\View;
class InvestorDashboardController extends Controller
{
    public function __construct(){ $this->middleware('permission:view investors'); }
    public function __invoke(): View
    {
        $totalInvestment=(float)Investment::sum('amount')-(float)InvestmentWithdrawal::sum('amount');
        $profitEarned=(float)ProfitPeriod::sum('total_investor_profit'); $profitPaid=(float)ProfitPayment::sum('amount');
        $stats=[
            ['label'=>'Total Investors','value'=>Investor::count(),'icon'=>'bi-people','color'=>'orange'],
            ['label'=>'Active Investors','value'=>Investor::where('status','active')->count(),'icon'=>'bi-person-check','color'=>'green'],
            ['label'=>'Current Investment','value'=>'Rs. '.number_format($totalInvestment,2),'icon'=>'bi-cash-stack','color'=>'blue'],
            ['label'=>'Pending Profit','value'=>'Rs. '.number_format(max(0,$profitEarned-$profitPaid),2),'icon'=>'bi-hourglass-split','color'=>'purple'],
        ];
        return view('admin.investor-dashboard.index',['stats'=>$stats,'latestInvestments'=>Investment::with('investor')->orderByDesc('investment_date')->orderByDesc('id')->limit(6)->get(),'latestPayments'=>ProfitPayment::with('investor')->orderByDesc('payment_date')->orderByDesc('id')->limit(6)->get()]);
    }
}
