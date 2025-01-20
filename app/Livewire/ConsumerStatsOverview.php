<?php

namespace App\Livewire;

use App\Models\Bill;
use App\Models\Reading;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ConsumerStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {

        $billPayable = Bill::query()->where('water_connection_id', auth()->user()->waterConnections()->first()?->id)->where('status', 'pending')->sum('billing_amount');

        $user = auth()->user();

        $firstConnection = $user->waterConnections()->first();

        $readingQuery = Reading::query();

        $billQuery = Bill::query();

        if ($firstConnection) {

            if ($firstConnection['pivot']['status'] === 'disconnected') {

                $disconnectedAt = $user->disconnectedUsers()->first()?->disconnected_at;

                $previousConsumption = $readingQuery
                    ->where('water_connection_id', $firstConnection->id)
                    ->where('created_at', '<', $disconnectedAt)
                    ->sum('previous_reading');

                $presentConsumption = $readingQuery
                    ->where('water_connection_id', $firstConnection->id)
                    ->where('created_at', '<', $disconnectedAt)
                    ->sum('present_reading');

                $billPayable = $billQuery
                    ->where('water_connection_id', $firstConnection->id)
                    ->where('created_at', '<', $disconnectedAt)
                    ->where('status', 'pending')
                    ->sum('billing_amount');
            } else {

                $previousConsumption = $readingQuery->where('water_connection_id', $firstConnection->id)->sum('previous_reading');

                $presentConsumption = $readingQuery->where('water_connection_id', $firstConnection->id)->sum('present_reading');

                $billPayable = $billQuery->where('water_connection_id', $firstConnection->id)->where('status', 'pending')->sum('billing_amount');
            }
        }

        return [
            Stat::make('Previous Consumption', $previousConsumption.' m³'),
            Stat::make('Present Consumption', $presentConsumption.' m³'),
            Stat::make('Bill Payable', '₱'.$billPayable),
        ];
    }
}
