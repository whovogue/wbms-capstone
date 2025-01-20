<?php

namespace App\Filament\Resources\WaterConnectionResousrceResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReadingsRelationManager extends RelationManager
{
    protected static string $relationship = 'readings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reader.name')
                    ->label('Reader'),
                TextColumn::make('previous_reading')->weight(FontWeight::Bold)->color('danger')->formatStateUsing(fn (string $state): string => $state.' m³'),
                TextColumn::make('present_reading')->weight(FontWeight::Bold)->color('success')->formatStateUsing(fn (string $state): string => $state.' m³'),
                TextColumn::make('total_consumption')->weight(FontWeight::Bold)->color('primary')->formatStateUsing(fn (string $state): string => $state.' m³'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date('F d, Y h:i A')
                    ->timezone('Asia/Manila'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
