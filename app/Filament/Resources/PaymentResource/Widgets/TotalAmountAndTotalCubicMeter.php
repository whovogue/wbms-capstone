<?php

namespace App\Filament\Resources\PaymentResource\Widgets;

use App\Filament\Resources\PaymentResource\Pages\ListPayments;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalAmountAndTotalCubicMeter extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getStats(): array
    {

        $totalConsumption = $this->getPageTableQuery()->with(['bill.reading'])->get()->sum('bill.reading.total_consumption');
        $totalAmount = $this->getPageTableQuery()->sum('amount');

        return [
            Stat::make('Total Water Consumption', $totalConsumption),
            Stat::make('Total Amount', '₱'.$totalAmount),
        ];
    }

    protected function getTablePage(): string
    {
        return ListPayments::class;
    }
}
