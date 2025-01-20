<?php

namespace App\Filament\Resources\BillerWaterConnectionViewResource\Pages;

use App\Filament\Resources\BillerWaterConnectionViewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBillerWaterConnectionViews extends ListRecords
{
    protected static string $resource = BillerWaterConnectionViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
