<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Investor;
use App\Models\ProfitPeriod;
use Illuminate\View\View;
class InvestorReportController extends Controller
{
    public function __construct(){ $this->middleware('permission:view investor reports'); }
    public function index(): View
    {
        $investors=Investor::withSum('investments as total_investment_sum','amount')->withSum('withdrawals as total_withdrawn_sum','amount')->withSum('allocations as total_profit_sum','profit_amount')->withSum('profitPayments as paid_profit_sum','amount')->latest()->get();
        $periodTotals=['sales'=>(float)ProfitPeriod::sum('total_sales'),'cost'=>(float)ProfitPeriod::sum('product_cost'),'expenses'=>(float)ProfitPeriod::sum('business_expenses'),'net'=>(float)ProfitPeriod::sum('net_profit'),'investors'=>(float)ProfitPeriod::sum('total_investor_profit'),'owner'=>(float)ProfitPeriod::sum('owner_profit')];
        return view('admin.investor-reports.index',compact('investors','periodTotals'));
    }
}
