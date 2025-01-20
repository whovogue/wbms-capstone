<?php

namespace App\Filament\Resources\WaterConnectionResource\Pages;

use App\Filament\Resources\WaterConnectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWaterConnections extends ListRecords
{
    protected static string $resource = WaterConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
