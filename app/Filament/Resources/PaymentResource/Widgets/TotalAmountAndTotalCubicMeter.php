<?php

namespace App\Filament\Resources\PaymentResource\Widgets;

use App\Filament\Resources\PaymentResource\Pages\ListPayments;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Filament\Forms\Components\Select;

class TotalAmountAndTotalCubicMeter extends BaseWidget
{
    use InteractsWithPageTable;

    public ?string $selectedMonth = null;
    public ?string $selectedYear = null;

    protected function getStats(): array
    {
        // Use the selected month and year, or default to the current month/year
        $queryDate = Carbon::create($this->selectedYear ?? now()->year, $this->selectedMonth ?? now()->month, 1);

        // Filter total consumption based on selected month/year
        $totalConsumption = $this->getPageTableQuery()
            ->whereHas('bill.reading', function ($query) use ($queryDate) {
                $query->whereMonth('created_at', $queryDate->month)
                      ->whereYear('created_at', $queryDate->year);
            })
            ->get()
            ->sum('bill.reading.total_consumption');

        // Filter total amount received based on selected month/year
        $totalAmount = $this->getPageTableQuery()
            ->whereMonth('created_at', $queryDate->month)
            ->whereYear('created_at', $queryDate->year)
            ->sum('partial_payment');

        // Filter total receivable amount based on selected month/year
        $totalAmountrecievable = $this->getPageTableQuery()
            ->whereMonth('created_at', $queryDate->month)
            ->whereYear('created_at', $queryDate->year)
            ->sum('amount');

        return [
            Stat::make("Total Water Consumption ({$queryDate->format('F Y')})", $totalConsumption . ' m³')
                ->description("Total Water Consumptions")
                ->color('success'),

            Stat::make("Received Payments ({$queryDate->format('F Y')})", '₱' . number_format($totalAmount, 2))
                ->description("Total Amount Received by WBMS")
                ->color('success'),

            Stat::make("Total Amount ({$queryDate->format('F Y')})", '₱' . number_format($totalAmountrecievable, 2))
                ->description("Total Expected Receivable Amount")
                ->color('success'),
        ];
    }

    protected function getTablePage(): string
    {
        return ListPayments::class;
    }
}
