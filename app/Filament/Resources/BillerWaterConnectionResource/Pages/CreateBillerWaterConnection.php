<?php

namespace App\Filament\Resources\BillerWaterConnectionResource\Pages;

use App\Filament\Resources\BillerWaterConnectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBillerWaterConnection extends CreateRecord
{
    protected static string $resource = BillerWaterConnectionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
