<?php

namespace App\Filament\Resources\RequestDocumentResource\Pages;

use App\Filament\Resources\RequestDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRequestDocuments extends ListRecords
{
    protected static string $resource = RequestDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
