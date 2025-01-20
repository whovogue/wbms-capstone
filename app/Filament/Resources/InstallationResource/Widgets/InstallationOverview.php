<?php

namespace App\Filament\Resources\InstallationResource\Widgets;

use App\Filament\Resources\InstallationResource\Pages\ListInstallations;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InstallationOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getStats(): array
    {
        $totalAmount = $this->getPageTableQuery()->sum('amount');

        return [
            Stat::make('Total Amount', $totalAmount),
        ];
    }

    protected function getTablePage(): string
    {
        return ListInstallations::class;
    }
}
