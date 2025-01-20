<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ConsumerSpendingChart extends ChartWidget
{
    protected static ?string $heading = 'Spending';

    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {

        $user = auth()->user();
        $firstConnection = $user->waterConnections()->first();
        $disconnectedAt = null;

        if ($firstConnection && $firstConnection['pivot']['status'] === 'disconnected') {
            // If the connection's status is disconnected, get the disconnected_at timestamp
            $disconnectedAt = $user->disconnectedUsers()->first()?->disconnected_at;
        }

        $monthlySpending = DB::table(DB::raw('(SELECT 1 AS month UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) AS months'))
            ->leftJoin(
                DB::raw('(
            SELECT 
                MONTH(created_at) as month, 
                SUM(amount) as total 
            FROM payments 
            WHERE YEAR(created_at) = YEAR(CURRENT_DATE) 
            AND water_connection_id = '.$firstConnection->id.'
            '.($disconnectedAt ? 'AND created_at < "'.$disconnectedAt.'"' : '').'
            GROUP BY month
        ) AS spending'),
                'months.month',
                '=',
                'spending.month'
            )
            ->select('months.month', DB::raw('COALESCE(spending.total, 0) as total'))
            ->orderBy('months.month')
            ->get()
            ->map(fn ($item) => $item->total);

        return [
            'datasets' => [
                [
                    'label' => 'Spending (₱)',
                    'data' => $monthlySpending,
                    'fill' => 'start',

                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
