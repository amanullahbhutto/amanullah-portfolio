<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhataTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KhataTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create khata transaction')->only('store');
        $this->middleware('permission:update khata transaction')->only('update');
        $this->middleware('permission:delete khata transaction')->only('destroy');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateTransaction($request);
        $validated['created_by'] = auth()->id();

        $transaction = KhataTransaction::query()->create($validated);

        $typeLabel = $transaction->type === KhataTransaction::TYPE_PESE_LIYE ? 'Pese Liye (Received)' : 'Pese Diye (Given)';

        return response()->json([
            'message' => "Transaction '{$typeLabel}' of Rs. " . number_format($transaction->amount, 2) . ' saved successfully.',
            'id' => $transaction->id,
            'transaction' => $transaction,
        ], 201);
    }

    public function update(Request $request, KhataTransaction $khataTransaction): JsonResponse
    {
        $validated = $this->validateTransaction($request);
        $khataTransaction->update($validated);

        return response()->json([
            'message' => 'Transaction updated successfully.',
            'transaction' => $khataTransaction,
        ]);
    }

    public function destroy(KhataTransaction $khataTransaction): JsonResponse
    {
        $khataTransaction->delete();

        return response()->json([
            'message' => 'Transaction deleted successfully.',
        ]);
    }

    private function validateTransaction(Request $request): array
    {
        return $request->validate([
            'khata_customer_id' => ['required', 'exists:khata_customers,id'],
            'type' => ['required', Rule::in([KhataTransaction::TYPE_PESE_LIYE, KhataTransaction::TYPE_PESE_DIYE])],
            'amount' => ['required', 'numeric', 'gt:0'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}

