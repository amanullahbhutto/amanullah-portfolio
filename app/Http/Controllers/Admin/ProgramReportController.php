<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\Program;
use Illuminate\View\View;
class ProgramReportController extends Controller
{
    public function __construct(){ $this->middleware('permission:view program reports'); }
    public function index(): View
    {
        $programs=Program::withCount('contributors')->withSum('contributions as total_received_sum','amount')->withSum('expenses as total_expenses_sum','amount')->latest()->get();
        $categories=ExpenseCategory::withSum('expenses as total_expenses_sum','amount')->latest()->get();
        return view('admin.program-reports.index',compact('programs','categories'));
    }
}
