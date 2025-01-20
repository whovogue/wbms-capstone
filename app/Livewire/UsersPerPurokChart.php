<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UsersPerPurokChart extends ChartWidget
{
    protected static ?string $heading = 'Users Per Purok';

    protected static ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $purokList = [
            '1',
            '1A',
            '2',
            '3A',
            '3B',
            '4A',
            '4B',
            '5A',
            '5B',
            '6',
            '6A1',
            '6B',
        ];

        $userCounts = User::whereIn('purok', $purokList)
            ->select('purok', DB::raw('count(*) as total'))
            ->groupBy('purok')
            ->pluck('total', 'purok')
            ->toArray();

        $completeData = [];
        foreach ($purokList as $purok) {
            $completeData[$purok] = $userCounts[$purok] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Consumers per Purok',
                    'data' => array_values($completeData),
                    'backgroundColor' => [
                        '#FF6384',
                        '#FF6384',
                        '#36A2EB',
                        '#36A2EB',
                        '#36A2EB',
                        '#FFCE56',
                        '#FFCE56',
                        '#FF6384',
                        '#FF6384',
                        '#36A2EB',
                        '#36A2EB',
                        '#36A2EB',
                    ],
                ],
            ],
            'labels' => array_keys($completeData),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'display' => false,
                ],
                'y' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
