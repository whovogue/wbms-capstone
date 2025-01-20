<?php

namespace App\Filament\Resources\BillerWaterConnectionViewResource\Pages;

use App\Filament\Resources\BillerWaterConnectionViewResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBillerWaterConnectionView extends ViewRecord
{
    protected static string $resource = BillerWaterConnectionViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
