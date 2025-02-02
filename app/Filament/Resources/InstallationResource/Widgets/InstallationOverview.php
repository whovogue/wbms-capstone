<?php

namespace App\Filament\Resources\InstallationResource\Widgets;

use App\Filament\Resources\InstallationResource\Pages\ListInstallations;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InstallationOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();
    
        // Total amount
        $totalAmount = $query->sum('amount');
    
        // Get the last 7 amounts for the chart
        $chartData = $query->latest('created_at')->limit(7)->pluck('amount')->toArray();
    
        return [
            Stat::make('Total Amount', '₱' . number_format($totalAmount, 2))
                ->description('Total revenue from installations')
                ->color('success')
                ->chart($chartData),
        ];
    }
    

    protected function getTablePage(): string
    {
        return ListInstallations::class;
    }
}
