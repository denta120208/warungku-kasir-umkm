<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Debt;
use App\Models\Expense;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        
        // Penjualan hari ini
        $todaySales = Sale::whereDate('created_at', $today)->sum('total');
        
        // Pengeluaran hari ini
        $todayExpenses = Expense::whereDate('created_at', $today)->sum('amount');
        
        // Total hutang belum lunas
        $unpaidDebts = Debt::where('status', '!=', 'paid')->sum('amount');
        
        return [
            Stat::make('Penjualan Hari Ini', 'Rp ' . number_format($todaySales, 0, ',', '.'))
                ->description('Total penjualan')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Pengeluaran Hari Ini', 'Rp ' . number_format($todayExpenses, 0, ',', '.'))
                ->description('Total pengeluaran')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
                
            Stat::make('Hutang Belum Lunas', 'Rp ' . number_format($unpaidDebts, 0, ',', '.'))
                ->description('Total hutang berjalan')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),
        ];
    }
}
