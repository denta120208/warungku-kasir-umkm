<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::with('user')
            ->where('user_id', $request->user()->id)
            ->orderBy('expense_date', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $expenses
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'required|in:supplies,utilities,rent,salary,other',
            'notes' => 'nullable|string',
        ]);

        $expense = Expense::create([
            'description' => $request->description,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'category' => $request->category,
            'notes' => $request->notes,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil ditambahkan',
            'data' => $expense->load('user')
        ], 201);
    }

    public function show(Expense $expense)
    {
        if ($expense->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $expense->load('user')
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        if ($expense->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'required|in:supplies,utilities,rent,salary,other',
            'notes' => 'nullable|string',
        ]);

        $expense->update([
            'description' => $request->description,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'category' => $request->category,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil diperbarui',
            'data' => $expense->load('user')
        ]);
    }

    public function destroy(Expense $expense)
    {
        if ($expense->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil dihapus'
        ]);
    }

    public function today(Request $request)
    {
        $expenses = Expense::where('user_id', $request->user()->id)
            ->whereDate('expense_date', Carbon::today())
            ->with('user')
            ->orderBy('expense_date', 'desc')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'expenses' => $expenses,
                'total_amount' => $totalExpenses,
                'total_items' => $expenses->count()
            ]
        ]);
    }

    public function byCategory(Request $request)
    {
        $expensesByCategory = Expense::where('user_id', $request->user()->id)
            ->select('category', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderBy('total_amount', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $expensesByCategory
        ]);
    }
}
