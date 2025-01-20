<?php

namespace App\Filament\Resources\BillerWaterConnectionViewResource\Pages;

use App\Filament\Resources\BillerWaterConnectionViewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBillerWaterConnectionView extends EditRecord
{
    protected static string $resource = BillerWaterConnectionViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
