<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaterConnectionResource\Pages;
use App\Filament\Resources\WaterConnectionResousrceResource\RelationManagers\BillsRelationManager;
use App\Filament\Resources\WaterConnectionResousrceResource\RelationManagers\ReadingsRelationManager;
use App\Filament\Resources\WaterConnectionResousrceResource\RelationManagers\UsersRelationManager;
use App\Models\Charge;
use App\Models\WaterConnection;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WaterConnectionResource extends Resource
{
    protected static ?string $model = WaterConnection::class;

    protected static ?string $navigationGroup = 'Water Connection';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $modelLabel = 'Connections';

    protected static ?string $recordTitleAttribute = 'reference_id';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    protected static ?string $navigationBadgeTooltip = 'Number of Pending Connections';

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('address')->required(),
                        Select::make('purok')
                            ->options([
                                '1' => '1',
                                '1A' => '1A',
                                '2' => '2',
                                '3A' => '3A',
                                '3B' => '3B',
                                '4A' => '4A',
                                '4B' => '4B',
                                '4C' => '4C',
                                '5' => '5',
                                '5A' => '5A',
                                '5B' => '5B',
                                '6A' => '6A',
                                '6A-1' => '6A-1',
                                '6B' => '6B',
                            ])
                            ->label('Purok')
                            ->required(),
                        TextInput::make('phone_number')->tel()->telRegex('/^(0|63)\d{10}$/'),
                        Select::make('status')->options([
                            'pending' => 'Pending',
                            'active' => 'Active',
                            'disconnected' => 'Disconnected',
                        ])
                            ->live()
                            ->hiddenOn('create'),
                        TextInput::make('amount')
                            ->required()
                            ->prefix('₱')
                            ->label('Installation Fee')
                            ->numeric()
                            ->default(123)
                            ->hiddenOn('create')
                            ->visible(function (Get $get, $record) {
                                return $get('status') != 'pending';
                            }),
                        Select::make('charge_id')
                            ->required()
                            ->label('Charge Type')->options(Charge::all()->pluck('name', 'id')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_id')->weight(FontWeight::Bold)->searchable()->copyable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                // Tables\Columns\TextColumn::make('address'),
                Tables\Columns\TextColumn::make('purok'),
                // Tables\Columns\TextColumn::make('phone_number'),
                // Tables\Columns\TextColumn::make('connected_date')
                //     ->formatStateUsing(fn (string $state) => is_null($state) ? 'N/A' : $state),
                // Tables\Columns\TextColumn::make('charge.name')
                //     ->label('Type'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'pending' => 'gray',
                    'active' => 'success',
                    'disconnected' => 'danger',
                })
                    ->formatStateUsing(fn (string $state): string => __(ucfirst($state))),
                TextColumn::make('created_at')->label('Date Created')->date('F d, Y h:i A')->timezone('Asia/Manila'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                ->label('Filter by Status')
                ->options(fn () => WaterConnection::query()
                    ->select('status')
                    ->distinct()
                    ->pluck('status', 'status') // Fetch unique roles
                    ->toArray()
                )
                ->searchable()
                ->preload()
                ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])->groups([
                Tables\Grouping\Group::make('purok')
                    ->collapsible(),
                    Tables\Grouping\Group::make('status')
                    ->collapsible(),

            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->latest();
            });
    }

    public static function getRelations(): array
    {
        return [
            ReadingsRelationManager::class,
            BillsRelationManager::class,
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWaterConnections::route('/'),
            'create' => Pages\CreateWaterConnection::route('/create'),
            'edit' => Pages\EditWaterConnection::route('/{record}/edit'),
            'view' => Pages\ViewWaterConnection::route('/{record}'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name'),
                TextEntry::make('email'),
            ]);
    }
}
