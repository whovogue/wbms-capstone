<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReadingResource\Pages;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Reading;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReadingResource extends Resource
{
    protected static ?string $model = Reading::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Water Connection';

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin() || auth()->user()->isMeterReader();
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
                TextColumn::make('waterConnection.reference_id')->weight(FontWeight::Bold)->searchable(),
                TextColumn::make('waterConnection.name')->label('Owner Name')->searchable(),
                TextColumn::make('previous_reading')->weight(FontWeight::Bold)->color('danger')->formatStateUsing(fn (string $state): string => $state.' m³'),
                TextColumn::make('present_reading')->weight(FontWeight::Bold)->color('success')->formatStateUsing(fn (string $state): string => $state.' m³'),
                TextColumn::make('total_consumption')->weight(FontWeight::Bold)->color('primary')->formatStateUsing(fn (string $state): string => $state.' m³'),
                TextColumn::make('reader.name')->label('Reader Name'),
                TextColumn::make('created_at')->label('Reading Date')->date('F d, Y h:i A')->timezone('Asia/Manila'),

            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Action::make('edit')
                    ->icon('heroicon-o-pencil')
                    ->modalWidth('sm')
                    ->action(function ($data, $record) {
                        $previousReading = $record->previous_reading;
                        $totalReading = $data['reading'] - $previousReading;

                        $bill = Bill::where('reading_id', $record->id)->first();
                        $payment = Payment::where('bill_id', $bill->id)->first();

                        $amount = $totalReading > 10 ? (($totalReading - 10) * 13) + 125 : 125;

                        $payment->update([
                            'amount' => $amount + 40,
                        ]);

                        $bill->update([
                            'billing_amount' => $amount + 40,
                        ]);

                        $record->update([
                            'present_reading' => $data['reading'],
                            'total_consumption' => $totalReading,
                            'previous_reading' => $previousReading,
                        ]);
                    })
                    ->form([
                        TextInput::make('reading')
                            ->label('Present Reading')
                            ->numeric()
                            ->minValue(function (Model $record) {

                                $lastReading = $record->previous_reading;

                                return $lastReading + 1 ?? 1;
                            })
                            ->required()
                            ->suffix('m³'),
                    ])
                    ->visible(function ($record) {
                        $bill = Bill::where('reading_id', $record->id)->first();

                        if ($bill->status == 'pending') {
                            return true;
                        }

                        return false;
                    }),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('waterConnection.name')
                    ->label('Owner Name')
                    ->collapsible(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->latest();
            });
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
            'index' => Pages\ListReadings::route('/'),
            'create' => Pages\CreateReading::route('/create'),
            'edit' => Pages\EditReading::route('/{record}/edit'),
        ];
    }
}
