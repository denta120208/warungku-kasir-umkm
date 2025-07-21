<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static string $view = 'filament.pages.dashboard';
    
    protected static ?string $title = 'Dashboard WarungKu';
    
    protected ?string $heading = 'Selamat Datang di WarungKu';
    
    protected static ?int $navigationSort = -2;
    
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardStats::class,
        ];
    }
}
