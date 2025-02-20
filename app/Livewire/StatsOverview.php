<?php

namespace App\Livewire;

use App\Models\Payment;
use App\Models\Reading;
use App\Models\User;
use App\Models\WaterConnection;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $currentMonth = now()->format('F');
        $currentYear = now()->year;
    
        $totalConsumers = WaterConnection::query()
        ->where('status', 'active')
        ->count();
        
        // Get revenue for the current month
        $revenue = Payment::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('partial_payment');
    
        // Get total consumption for the current month
        $totalConsumptions = Reading::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_consumption');
    
        return [
            Stat::make("Consumption ({$currentMonth} {$currentYear})", $totalConsumptions . ' m³')
                ->description("Total Consumptions")
                ->color('success'),
    
            Stat::make("Revenue ({$currentMonth} {$currentYear})", '₱' . number_format($revenue, 2))
                ->description("Total Received Payments")
                ->color('success'),
    
            Stat::make('Water Connections', $totalConsumers)
                ->description("Total Number of Active Water Connections")
                ->color('success'),
        ];
    }
    
    
}
