<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class WaterConsumptionChart extends ChartWidget
{
    protected static ?string $heading = 'Water Consumption';

    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $monthlyConsumption = DB::table(DB::raw('(SELECT 1 AS month UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) AS months'))
            ->leftJoin(DB::raw('(SELECT MONTH(created_at) as month, SUM(total_consumption) as total FROM readings WHERE YEAR(created_at) = YEAR(CURRENT_DATE) GROUP BY month) AS consumption'), 'months.month', '=', 'consumption.month')
            ->select('months.month', DB::raw('COALESCE(consumption.total, 0) as total'))
            ->orderBy('months.month')
            ->get()
            ->map(fn ($item) => $item->total);

        return [
            'datasets' => [
                [
                    'label' => 'Water Consumption (m³)',
                    'data' => $monthlyConsumption,
                    'fill' => 'start',

                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
