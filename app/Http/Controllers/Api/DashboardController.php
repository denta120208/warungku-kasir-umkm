<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Debt;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $userId = $request->user()->id;
        $today = Carbon::today();

        // Today's sales
        $todaySales = Sale::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->sum('total');

        // Today's expenses
        $todayExpenses = Expense::where('user_id', $userId)
            ->whereDate('expense_date', $today)
            ->sum('amount');

        // Unpaid debts
        $unpaidDebts = Debt::where('user_id', $userId)
            ->unpaid()
            ->sum('amount');

        // Total sales this month
        $monthlySales = Sale::where('user_id', $userId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total');

        // Total expenses this month
        $monthlyExpenses = Expense::where('user_id', $userId)
            ->whereMonth('expense_date', Carbon::now()->month)
            ->whereYear('expense_date', Carbon::now()->year)
            ->sum('amount');

        // Recent sales (last 5)
        $recentSales = Sale::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent expenses (last 5)
        $recentExpenses = Expense::where('user_id', $userId)
            ->orderBy('expense_date', 'desc')
            ->limit(5)
            ->get();

        // Overdue debts
        $overdueDebts = Debt::where('user_id', $userId)
            ->unpaid()
            ->where('due_date', '<', $today)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'today' => [
                    'sales' => $todaySales,
                    'expenses' => $todayExpenses,
                    'profit' => $todaySales - $todayExpenses
                ],
                'monthly' => [
                    'sales' => $monthlySales,
                    'expenses' => $monthlyExpenses,
                    'profit' => $monthlySales - $monthlyExpenses
                ],
                'debts' => [
                    'unpaid_amount' => $unpaidDebts,
                    'overdue_count' => $overdueDebts
                ],
                'recent_activities' => [
                    'sales' => $recentSales,
                    'expenses' => $recentExpenses
                ]
            ]
        ]);
    }
}
