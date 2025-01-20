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

        $totalConsumers = User::query()->where('role', 'consumers')->count();
        // $totalConnections = WaterConnection::query()->where('status', 'active')->count();
        $revenue = Payment::query()->sum('amount');
        $totalConsumptions = Reading::query()->sum('total_consumption');

        return [
            // Stat::make('Total Consumers', $totalConsumers),
            // Stat::make('Total Connections', $totalConnections),
            Stat::make('Consumption', $totalConsumptions.' m³'),
            Stat::make('Revenue', '₱'.$revenue),
            // Stat::make('Total Water Consumption', $totalConsumptions.' m³'),
            Stat::make('Water Consumers', $totalConsumers),
        ];
    }
}
