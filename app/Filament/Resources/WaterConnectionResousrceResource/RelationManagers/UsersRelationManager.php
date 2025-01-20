<?php

namespace App\Filament\Resources\WaterConnectionResousrceResource\RelationManagers;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'pending' => 'gray',
                    'active' => 'success',
                    'disconnected' => 'danger',
                })
                    ->formatStateUsing(fn (string $state): string => __(ucfirst($state))),
                TextColumn::make('created_at')->label('Date Joined')->date('F d, Y h:i A')->timezone('Asia/Manila'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('add')
                    ->label('Add User')
                    ->form([
                        Select::make('user_id')
                            ->label('Users')
                            ->preload()
                            ->hiddenOn('create')
                            ->multiple()
                            ->searchable()
                            ->options(function () {
                                return User::whereDoesntHave('waterConnections')
                                    ->whereNot('id', auth()->user()->id)
                                    ->where('role', 'consumers')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->afterStateUpdated(function ($state, $livewire) {
                                $selectedUsers = User::whereIn('id', $state)->get();

                                Notification::make()
                                    ->title(auth()->user()->name.' Added you to '.$livewire->getOwnerRecord()['reference_id'].' water connection')
                                    ->success()
                                    ->sendToDatabase($selectedUsers);
                            }),
                    ])
                    ->action(
                        function ($data, $livewire) {
                            foreach ($data['user_id'] as $key => $value) {
                                $livewire->getOwnerRecord()->users()->attach($value, [
                                    'status' => 'active',
                                ]);
                            }
                        }

                    ),
            ])
            ->actions([
                Action::make('accept')
                    ->icon('heroicon-o-bars-4')
                    ->requiresConfirmation()
                    ->label('Action')
                    ->form([
                        Select::make('accept')
                            ->label('')
                            ->required()
                            ->default('activate')
                            ->options([
                                'active' => 'Activate',
                                'disconnected' => 'Disconnect',
                                'pending' => 'Pending',
                            ]),
                    ])
                    ->action(function ($record, $livewire, $data) {

                        $record->waterConnections()->where([
                            ['water_connection_id', $livewire->getOwnerRecord()['id']],
                            ['user_id', $record->user_id],
                        ])
                            ->update([
                                'water_connections_users.status' => $data['accept'],
                            ]);

                        if ($data['accept'] === 'disconnected') {
                            $record->disconnectedUsers()->updateOrCreate(
                                ['user_id' => $record->user_id],
                                ['disconnected_at' => now()]
                            );
                        }

                        $type = $data['accept'] === 'active' ? 'activate' : ($data['accept'] === 'disconnected' ? 'disconnect' : 'hold');

                        $status = $data['accept'] === 'active' ? Notification::make()->success() : ($data['accept'] === 'disconnected' ? Notification::make()->danger() : Notification::make()->warning());

                        $status->title(auth()->user()->name.' '.$type.' your '.$livewire->getOwnerRecord()['reference_id'].' water connection')
                            ->sendToDatabase(User::where('id', $record->user_id)->get());
                    }),
                DetachAction::make()
                    ->label('Remove')
                    ->action(function ($record, $livewire) {
                        Notification::make()
                            ->danger()
                            ->title(auth()->user()->name.' Remove you from '.$livewire->getOwnerRecord()['reference_id'].' water connection')->sendToDatabase(User::where('id', $record->user_id)->get());

                        $livewire->getOwnerRecord()->users()->detach($record->user_id);
                    }),

            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
