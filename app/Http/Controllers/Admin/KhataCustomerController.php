<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhataCustomer;
use App\Models\KhataTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KhataCustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view khata')->only(['index', 'show']);
        $this->middleware('permission:create khata customer')->only('store');
        $this->middleware('permission:update khata customer')->only('update');
        $this->middleware('permission:delete khata customer')->only('destroy');
    }

    public function index(Request $request): View
    {
        $q = trim($request->string('q')->toString());
        $status = trim($request->string('status')->toString());

        $customers = KhataCustomer::query()
            ->withSum(['transactions as total_pese_liye_sum' => fn ($query) => $query->where('type', KhataTransaction::TYPE_PESE_LIYE)], 'amount')
            ->withSum(['transactions as total_pese_diye_sum' => fn ($query) => $query->where('type', KhataTransaction::TYPE_PESE_DIYE)], 'amount')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('address', 'like', "%{$q}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $totalCustomers = KhataCustomer::query()->count();
        $totalOpeningBalance = (float) KhataCustomer::query()->sum('opening_balance');
        $totalPeseLiyeAll = (float) KhataTransaction::query()->where('type', KhataTransaction::TYPE_PESE_LIYE)->sum('amount');
        $totalPeseDiyeAll = (float) KhataTransaction::query()->where('type', KhataTransaction::TYPE_PESE_DIYE)->sum('amount');
        $totalNetBalance = $totalOpeningBalance + $totalPeseDiyeAll - $totalPeseLiyeAll;

        return view('admin.khata.index', compact(
            'customers',
            'q',
            'status',
            'totalCustomers',
            'totalPeseLiyeAll',
            'totalPeseDiyeAll',
            'totalNetBalance'
        ));
    }

    public function show(Request $request, KhataCustomer $khataCustomer): View
    {
        $q = trim($request->string('q')->toString());
        $type = trim($request->string('type')->toString());
        $startDate = trim($request->string('start_date')->toString());
        $endDate = trim($request->string('end_date')->toString());

        // Get full chronological sequence to compute accurate running balances
        $allTransactions = $khataCustomer->transactions()
            ->with('creator')
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = (float) $khataCustomer->opening_balance;
        $ledgerItems = collect();

        foreach ($allTransactions as $trx) {
            $amount = (float) $trx->amount;
            $peseLiye = $trx->type === KhataTransaction::TYPE_PESE_LIYE ? $amount : 0;
            $peseDiye = $trx->type === KhataTransaction::TYPE_PESE_DIYE ? $amount : 0;

            // Running balance: Opening + Total Given (Diye) - Total Received (Liye)
            $runningBalance += ($peseDiye - $peseLiye);

            $trx->pese_liye = $peseLiye;
            $trx->pese_diye = $peseDiye;
            $trx->running_balance = $runningBalance;

            $ledgerItems->push($trx);
        }

        // Apply filters for display while keeping running balance accurate
        $filteredLedger = $ledgerItems->filter(function ($trx) use ($q, $type, $startDate, $endDate) {
            if ($type !== '' && $trx->type !== $type) {
                return false;
            }

            if ($startDate !== '' && $trx->transaction_date->format('Y-m-d') < $startDate) {
                return false;
            }

            if ($endDate !== '' && $trx->transaction_date->format('Y-m-d') > $endDate) {
                return false;
            }

            if ($q !== '' && !str_contains(strtolower((string) $trx->description), strtolower($q))) {
                return false;
            }

            return true;
        })->reverse(); // Display most recent first

        $totalPeseLiye = (float) $khataCustomer->transactions()->where('type', KhataTransaction::TYPE_PESE_LIYE)->sum('amount');
        $totalPeseDiye = (float) $khataCustomer->transactions()->where('type', KhataTransaction::TYPE_PESE_DIYE)->sum('amount');
        $currentBalance = (float) $khataCustomer->opening_balance + $totalPeseDiye - $totalPeseLiye;

        return view('admin.khata.show', compact(
            'khataCustomer',
            'filteredLedger',
            'totalPeseLiye',
            'totalPeseDiye',
            'currentBalance',
            'q',
            'type',
            'startDate',
            'endDate'
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateCustomer($request);
        $customer = KhataCustomer::query()->create($data);

        return response()->json([
            'message' => 'Customer added to Khata successfully.',
            'id' => $customer->id,
            'customer' => $customer,
        ], 201);
    }

    public function update(Request $request, KhataCustomer $khataCustomer): JsonResponse
    {
        $data = $this->validateCustomer($request);
        $khataCustomer->update($data);

        return response()->json([
            'message' => 'Khata customer updated successfully.',
            'customer' => $khataCustomer,
        ]);
    }

    public function destroy(KhataCustomer $khataCustomer): JsonResponse
    {
        $khataCustomer->delete();

        return response()->json([
            'message' => 'Customer and associated Khata records deleted successfully.',
        ]);
    }

    private function validateCustomer(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}

