<?php

namespace App\Livewire;

use App\Models\WaterConnection;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BillerActiveWaterConnection extends BaseWidget
{
    protected function getStats(): array
    {

        $reader = auth()->user();

        $activeConnections = WaterConnection::where('status', 'active')->count();

        $unreadConnections = WaterConnection::where('status', 'active')->whereHas('bills', function ($query) {
            $query->where('status', 'pending');
        })->count();

        // $readConnections = WaterConnection::where('status', 'active')->where('bills.status', 'paid')->count();

        return [
            Stat::make('Total Active Connections', $activeConnections),
            // Stat::make('Unread Water Connections', $unreadConnections),
            // Stat::make('Read Water Connections', $readConnections),

        ];
    }
}
