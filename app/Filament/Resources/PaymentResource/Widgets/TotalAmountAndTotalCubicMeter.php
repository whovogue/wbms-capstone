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
        // $totalAmount = $this->getPageTableQuery()->sum('amount');
        $totalAmount = $this->getPageTableQuery()->sum('partial_payment');
        $totalAmountrecievable = $this->getPageTableQuery()->sum('amount');


        return [
            Stat::make('Total Water Consumption', $totalConsumption . ' m³')
                ->description('Total Water Consumptions of all Water connections')
                ->color('success'),

            Stat::make('Received Payments', '₱' . number_format($totalAmount, 2))
                ->description('Total Amount Received by WBMS')
                ->color('success'),

            Stat::make('Total Amount', '₱' . number_format($totalAmountrecievable, 2))
                ->description('Total Expected Receivable Amount')
                ->color('success'),
        ];
    }

    protected function getTablePage(): string
    {
        return ListPayments::class;
    }
}
