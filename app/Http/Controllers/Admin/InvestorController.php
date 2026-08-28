<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvestorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view investors')->only('index');
        $this->middleware('permission:create investor')->only('store');
        $this->middleware('permission:update investor')->only('update');
        $this->middleware('permission:delete investor')->only('destroy');
    }

    public function index(Request $request): View
    {
        $q = trim($request->string('q')->toString());
        $status = trim($request->string('status')->toString());
        $investors = Investor::query()
            ->withSum('investments as total_investment_sum', 'amount')
            ->withSum('withdrawals as total_withdrawn_sum', 'amount')
            ->withSum('allocations as total_profit_sum', 'profit_amount')
            ->withSum('profitPayments as paid_profit_sum', 'amount')
            ->when($q !== '', fn ($query) => $query->where(fn ($sub) => $sub->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")->orWhere('cnic_reference', 'like', "%{$q}%")))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.investors.index', compact('investors', 'status'));
    }

    public function store(Request $request): JsonResponse
    {
        $investor = Investor::query()->create($this->validated($request));
        return response()->json(['message' => 'Investor added successfully.', 'id' => $investor->id], 201);
    }

    public function update(Request $request, Investor $investor): JsonResponse
    {
        $investor->update($this->validated($request));
        return response()->json(['message' => 'Investor updated successfully.']);
    }

    public function destroy(Investor $investor): JsonResponse
    {
        if ($investor->investments()->exists() || $investor->allocations()->exists() || $investor->profitPayments()->exists() || $investor->withdrawals()->exists()) {
            $investor->update(['status' => 'inactive']);
            return response()->json(['message' => 'Investor has financial history, so the investor was marked inactive instead of being deleted.']);
        }
        $investor->delete();
        return response()->json(['message' => 'Investor deleted successfully.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required','string','max:150'],
            'phone' => ['nullable','string','max:50'],
            'email' => ['nullable','email','max:255'],
            'cnic_reference' => ['nullable','string','max:100'],
            'profit_share_percentage' => ['required','numeric','min:0','max:100'],
            'joining_date' => ['nullable','date'],
            'status' => ['required', Rule::in(['active','inactive','on_hold'])],
            'notes' => ['nullable','string','max:3000'],
        ]);
    }
}
