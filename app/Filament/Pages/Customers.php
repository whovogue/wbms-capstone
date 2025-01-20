<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Customers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static string $view = 'filament.pages.customers';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Residents';

    protected ?string $heading = 'Residents';

    public static function canAccess(): bool
    {
        return auth()->user()->isClerk();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->where('role', 'consumers'))
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }
}
