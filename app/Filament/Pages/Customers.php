<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;


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
                TextColumn::make('name')
                ->searchable()
                ->sortable(),
                TextColumn::make('email')
                ->searchable()
                ->sortable(),
                TextColumn::make('purok')
                ->sortable(),
            ])
            ->filters([
                SelectFilter::make('purok')
                    ->label('Filter by Purok')
                    ->options([
                        '1' => '1',
                        '1A' => '1A',
                        '2' => '2',
                        '3A' => '3A',
                        '3B' => '3B',
                        '4A' => '4A',
                        '4B' => '4B',
                        '5A' => '5A',
                        '5B' => '5B',
                        '6' => '6',
                        '6A1' => '6A1',
                        '6B' => '6B',
                    ])
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }
}
