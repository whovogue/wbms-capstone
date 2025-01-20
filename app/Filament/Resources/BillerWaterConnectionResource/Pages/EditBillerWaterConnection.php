<?php

namespace App\Filament\Resources\BillerWaterConnectionResource\Pages;

use App\Filament\Resources\BillerWaterConnectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBillerWaterConnection extends EditRecord
{
    protected static string $resource = BillerWaterConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
