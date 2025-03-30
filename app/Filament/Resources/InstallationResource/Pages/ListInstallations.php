<?php

namespace App\Filament\Resources\InstallationResource\Pages;

use App\Filament\Resources\InstallationResource;
use App\Filament\Resources\InstallationResource\Widgets\InstallationOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInstallations extends ListRecords
{
    protected static string $resource = InstallationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         InstallationOverview::class,
    //     ];
    // }
}
