<?php

namespace App\Filament\Resources\BillerWaterConnectionViewResource\Pages;

use App\Filament\Resources\BillerWaterConnectionViewResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBillerWaterConnectionView extends CreateRecord
{
    protected static string $resource = BillerWaterConnectionViewResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
