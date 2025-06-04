<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonnelResource\Pages;
use App\Filament\Resources\PersonnelResource\RelationManagers;
use App\Models\Personnel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextArea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;


class PersonnelResource extends Resource
{
    protected static ?string $model = Personnel::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Management';

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin() || auth()->user()->isClerk();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
            TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(255),
            Select::make('position')
                ->label('Position')
                ->required()
                ->options([
                    'Barangay Kapitan' => 'Barangay Kapitan',
                    'Barangay Kagawad' => 'Barangay Kagawad',
                    'SK Chairman' => 'SK Chairman',
                    'IPMR' => 'IPMR',
                ]),
            SignaturePad::make('signature')
                ->label(__('Sign here (Optional)'))
                ->dotSize(2.0)
                ->lineMinWidth(0.5)
                ->lineMaxWidth(2.5)
                ->throttle(16)
                ->minDistance(5)
                ->velocityFilterWeight(0.7)
                ->confirmable(),
        ])
        ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('signature')
                ->label('Signature')
                ->size(40),
                TextColumn::make('name')
                ->searchable()
                ->sortable(),
                TextColumn::make('position')
                    ->searchable()
                    ->sortable()
                    ->badge() // Makes it a pill
                    ->color(fn (string $state): string => match ($state) {
                        'Barangay Kapitan' => 'success',
                        'Barangay Kagawad' => 'info',
                        'SK Chairman' => 'warning',
                        'IPMR' => 'purple',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Barangay Kapitan' => 'Barangay Kapitan',
                        'Barangay Kagawad' => 'Barangay Kagawad',
                        'SK Chairman' => 'SK Chairman',
                        'IPMR' => 'IPMR',
                        default => ucfirst($state),
                    }),
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPersonnels::route('/'),
            'create' => Pages\CreatePersonnel::route('/create'),
            'edit' => Pages\EditPersonnel::route('/{record}/edit'),
        ];
    }
}
