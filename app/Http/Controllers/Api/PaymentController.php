<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Debt;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['debt', 'user'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'debt_id' => 'required|exists:debts,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qris,bank_transfer',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $debt = Debt::where('id', $request->debt_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$debt) {
            return response()->json([
                'success' => false,
                'message' => 'Hutang tidak ditemukan'
            ], 404);
        }

        if (!$debt->canAcceptPayment($request->amount)) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pembayaran melebihi sisa hutang'
            ], 422);
        }

        $payment = Payment::create([
            'debt_id' => $request->debt_id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil ditambahkan',
            'data' => $payment->load(['debt', 'user'])
        ], 201);
    }

    public function show(Payment $payment)
    {
        if ($payment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $payment->load(['debt', 'user'])
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        if ($payment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qris,bank_transfer',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $debt = $payment->debt;
        $oldAmount = $payment->amount;
        $newAmount = $request->amount;
        $remainingAmount = $debt->remaining_amount + $oldAmount;

        if ($newAmount > $remainingAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pembayaran melebihi sisa hutang'
            ], 422);
        }

        $payment->update([
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil diperbarui',
            'data' => $payment->load(['debt', 'user'])
        ]);
    }

    public function destroy(Payment $payment)
    {
        if ($payment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dihapus'
        ]);
    }

    public function debtPayments(Request $request, Debt $debt)
    {
        if ($debt->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        $payments = Payment::with(['debt', 'user'])
            ->where('debt_id', $debt->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'debt' => $debt,
                'payments' => $payments,
                'total_paid' => $payments->sum('amount'),
                'remaining_amount' => $debt->remaining_amount
            ]
        ]);
    }
}
