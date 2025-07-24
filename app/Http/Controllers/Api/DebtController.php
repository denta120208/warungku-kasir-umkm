<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $debts = Debt::with(['user', 'payments'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $debts
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string',
        ]);

        $debt = Debt::create([
            'customer_name' => $request->customer_name,
            'amount' => $request->amount,
            'paid_amount' => 0,
            'due_date' => $request->due_date,
            'status' => 'unpaid',
            'notes' => $request->notes,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hutang berhasil ditambahkan',
            'data' => $debt->load(['user', 'payments'])
        ], 201);
    }

    public function show(Debt $debt)
    {
        if ($debt->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $debt->load(['user', 'payments'])
        ]);
    }

    public function update(Request $request, Debt $debt)
    {
        if ($debt->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $debt->update([
            'customer_name' => $request->customer_name,
            'amount' => $request->amount,
            'due_date' => $request->due_date,
            'notes' => $request->notes,
        ]);

        $debt->updateStatus();

        return response()->json([
            'success' => true,
            'message' => 'Hutang berhasil diperbarui',
            'data' => $debt->load(['user', 'payments'])
        ]);
    }

    public function destroy(Debt $debt)
    {
        if ($debt->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        $debt->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hutang berhasil dihapus'
        ]);
    }

    public function unpaid(Request $request)
    {
        $debts = Debt::with(['user', 'payments'])
            ->where('user_id', $request->user()->id)
            ->unpaid()
            ->orderBy('due_date', 'asc')
            ->get();

        $totalUnpaid = $debts->sum('remaining_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'debts' => $debts,
                'total_unpaid' => $totalUnpaid,
                'count' => $debts->count()
            ]
        ]);
    }

    public function overdue(Request $request)
    {
        $debts = Debt::with(['user', 'payments'])
            ->where('user_id', $request->user()->id)
            ->unpaid()
            ->where('due_date', '<', Carbon::today())
            ->orderBy('due_date', 'asc')
            ->get();

        $totalOverdue = $debts->sum('remaining_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'debts' => $debts,
                'total_overdue' => $totalOverdue,
                'count' => $debts->count()
            ]
        ]);
    }
}
