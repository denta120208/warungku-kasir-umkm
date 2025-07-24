<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\DebtController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\DashboardController;

// Public routes - No token required (session based)
Route::post('/login', [AuthController::class, 'loginSimple']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logoutSimple']);
Route::get('/user', [AuthController::class, 'userSimple']);

// Dashboard
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

// Sales
Route::apiResource('sales', SaleController::class);
Route::get('/sales/today', [SaleController::class, 'today']);

// Debts
Route::apiResource('debts', DebtController::class);
Route::get('/debts/unpaid', [DebtController::class, 'unpaid']);
Route::get('/debts/overdue', [DebtController::class, 'overdue']);

// Expenses
Route::apiResource('expenses', ExpenseController::class);
Route::get('/expenses/today', [ExpenseController::class, 'today']);
Route::get('/expenses/by-category', [ExpenseController::class, 'byCategory']);

// Payments
Route::apiResource('payments', PaymentController::class);
Route::get('/debts/{debt}/payments', [PaymentController::class, 'debtPayments']);

// Token-based routes (optional for mobile apps)
Route::prefix('token')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});
