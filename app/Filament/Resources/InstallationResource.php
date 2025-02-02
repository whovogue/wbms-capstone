<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstallationResource\Pages;
use App\Filament\Resources\InstallationResource\Widgets\InstallationOverview;
use App\Models\Installation;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InstallationResource extends Resource
{
    protected static ?string $model = Installation::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';

    protected static ?string $navigationGroup = 'Histories';

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('waterConnection.reference_id')->weight(FontWeight::Bold),
                TextColumn::make('waterConnection.name')->label('Consumer Name'),
                TextColumn::make('type')
                ->label('Installation Status') // Custom label
                ->formatStateUsing(fn (string $state): string => ucfirst($state))
                ->badge() // Turns it into a pill
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'active' => 'success',
                    'disconnected' => 'danger',
                    default => 'gray',
                }),            
                // TextColumn::make('waterConnection.amount')->money('PHP')->weight(FontWeight::Bold)->toggleable(),
                TextColumn::make('waterConnection.amount')->money('PHP')->weight(FontWeight::Bold),
                TextColumn::make('created_at')->label('Payment Date')->date('F d, Y h:i A')->timezone('Asia/Manila'),
            ])
            ->filters([

                

            ])
            // ->sortable()
            // ->searchable()
            // ->searchPlaceholder('Search Name...')
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstallations::route('/'),
            'create' => Pages\CreateInstallation::route('/create'),
            'edit' => Pages\EditInstallation::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            InstallationOverview::class,
        ];
    }
}
