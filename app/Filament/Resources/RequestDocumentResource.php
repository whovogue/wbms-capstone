<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequestDocumentResource\Pages;
use App\Models\RequestDocument;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Toggle;
use App\Models\Personnel;

class RequestDocumentResource extends Resource
{
    protected static ?string $model = RequestDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationGroup = 'Management';

    // NAVIGATION BADGE

    public static function getNavigationBadge(): ?string
    {
        // Ensure only admins or clerks see the badge
        if (!(auth()->user()?->isAdmin() || auth()->user()?->isClerk())) {
            return null;
}

    
        // Count the number of pending RequestDocuments
        $pendingCount = RequestDocument::where('status', 'pending')->count();
    
        // Return the count or null if there are no pending requests
        return $pendingCount > 0 ? (string) $pendingCount : null;
    }
    

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    protected static ?string $navigationBadgeTooltip = 'The number of pending Requests';

    // NAVIGATION BADGE END
    
    public static function canViewAny(): bool
    {
        return ! auth()->user()->isMeterReader();
    }

    public static function canCreate(): bool
    {
        return ! auth()->user()->isMeterReader();
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();

        if ($user->isConsumer() && $record->status === 'pending') {
            return true;
        } else {

            if ($record->status === 'approved') {
                return false;
            }

            if ($user->isAdmin() || $user->isClerk()) {
                return true;
            }

            return false;
        }
    }

    public static function form(Form $form): Form
    {
        if (auth()->user()->isConsumer()) {
            return $form
                ->schema([
                    Group::make()->schema([
                        Section::make()->schema([
                            TextInput::make('custom_fields.name')
                                ->label('Name')
                                ->default(auth()->user()->name)
                                ->required(),
                            Select::make('custom_fields.purok')
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
                                    '6A1' => '6A1',
                                    '6B' => '6B',
                                ])
                                ->default(auth()->user()->purok)
                                ->label('Purok')
                                ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                                ->required(),
                            Select::make('custom_fields.civil_status')
                                ->label('Civil Status')
                                ->default(auth()->user()->civil_status)
                                ->options([
                                    'single' => 'Single',
                                    'married' => 'Married',
                                    'widowed' => 'Widowed',
                                    'divorced' => 'Divorced',
                                ])
                                ->required(),
                            TextInput::make('custom_fields.citizenship')
                                ->label('Citizenship')
                                ->default(auth()->user()->citizenship)
                                ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                                ->required(),
                            Select::make('custom_fields.gender')
                                ->label('Gender')
                                ->default(auth()->user()->gender)
                                ->options([
                                    'Male' => 'Male',
                                    'Female' => 'Female',
                                    'Prefer not to say' => 'Prefer not to say',
                                ])
                                ->required(),

                            TextInput::make('custom_fields.blood_type')
                                ->label('Blood Type')
                                ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                                ->required(false),

                            TextInput::make('custom_fields.weight')
                                ->label('Weight')
                                ->suffix('kg')
                                ->numeric()
                                ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                                ->required(),
                            TextInput::make('custom_fields.height')
                                ->label('Height')
                                ->suffix('Ft')
                                // ->numeric()
                                ->rule('regex:/^[0-9\'\"\.]+$/')
                                ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                                ->required(),
                            DatePicker::make('custom_fields.date_of_birth')
                                ->label('Date Of Birth')
                                ->default(auth()->user()->date_of_birth)
                                ->required(),
                                // NEW ADDED CERT NO.
                                TextInput::make('custom_fields.cert_no')
                                ->label('CERT No. (Leave Blank if not applicable)')
                                ->default(auth()->user()->cert_no)
                                ->visible(fn (callable $get) => ($get('type') === 'barangay_clearance')),
                                // NEW ADDED CERT NO.
                            TextInput::make('custom_fields.address')
                                ->label('Address (Purok, Barangay, City/Municipality)')
                                ->default(auth()->user()->address)
                                ->visible(fn (callable $get) => ($get('type') === 'barangay_clearance'))
                                ->required(),
                            TextInput::make('custom_fields.purpose')
                                ->label('Purpose')
                                ->visible(fn (callable $get) => ($get('type') === 'barangay_clearance'))
                                ->required(),
                        ])->columns(2),

                        Section::make('Person Incase of Emergency')->schema([
                            TextInput::make('custom_fields.emergency_name')
                                ->label('Name')
                                ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                                ->required(),
                            TextInput::make('custom_fields.emergency_relation')
                                ->label('Relation')
                                ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                                ->required(),
                            TextInput::make('custom_fields.emergency_address')
                                ->label('Address (Purok, Barangay, City/Municipality)')
                                ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                                ->required(),
                            TextInput::make('custom_fields.emergency_contact_number')
                                ->label('Contact Number')
                                ->tel()->telRegex('/^(0|63)\d{10}$/')
                                ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                                ->required(),
                        ])
                            ->columns(2)
                            ->visible(fn (callable $get) => ($get('type') === 'barangay_id')),
                    ])
                        ->columnSpan(2),

                    Group::make()->schema([
                        Section::make()->schema([
                            Select::make('type')
                                ->required()
                                ->disabledOn('edit')
                                ->reactive()
                                ->options([
                                    'barangay_id' => 'Barangay ID',
                                    'barangay_clearance' => 'Barangay Clearance',
                                ]),
                            Select::make('status')
                                ->required()
                                ->visible(auth()->user()->isAdmin() || auth()->user()->isClerk())
                                ->default('pending')
                                ->options([
                                    'pending' => 'Pending',
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                ]),
                        ]),
                    ]),
                ])
                ->columns(3);
        } else {
            return $form
                ->schema(RequestDocumentResource::newForm())->columns(3);
        }
    }

    public static function newForm()
    {
        return [
            Group::make()->schema([
                Section::make()->schema([

                    TextInput::make('custom_fields.name')
                        ->label('Name')
                        ->required(),
                    Select::make('custom_fields.purok')
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
                        ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                        ->required(),
                    Select::make('custom_fields.civil_status')
                        ->label('Civil Status')
                        ->options([
                            'single' => 'Single',
                            'married' => 'Married',
                            'widowed' => 'Widowed',
                            'divorced' => 'Divorced',
                        ])
                        ->required(),
                    TextInput::make('custom_fields.citizenship')
                        ->label('Citizenship')
                        ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                        ->required(),
                    Select::make('custom_fields.gender')
                        ->label('Gender')
                        ->options([
                            'Male' => 'Male',
                            'Female' => 'Female',
                            'Prefer not to say' => 'Prefer not to say',
                        ])
                        ->required(),

                    TextInput::make('custom_fields.blood_type')
                        ->label('Blood Type')
                        ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                        ->required(false),

                    TextInput::make('custom_fields.weight')
                        ->label('Weight')
                        ->suffix('kg')
                        ->numeric()
                        ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                        ->required(),
                    TextInput::make('custom_fields.height')
                        ->label('Height')
                        ->suffix('ft')
                        // ->numeric()
                        ->rule('regex:/^[0-9\'\"\.]+$/')
                        ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                        ->required(),
                    DatePicker::make('custom_fields.date_of_birth')
                        ->label('Date Of Birth')
                        ->required(),
                        // TextInput::make('custom_fields.control_number')
                        // ->label('Control Number')
                        // ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                        // ->required(),
                    // NEW ADDED CERT NO.
                    TextInput::make('custom_fields.cert_no')
                    ->label('CERT No. (Leave Blank if not applicable)')
                    // ->default(auth()->user()->cert_no)
                    ->visible(fn (callable $get) => ($get('type') === 'barangay_clearance')),
                    TextInput::make('custom_fields.DPI')
                    ->label('Date & Place of Issuance')
                    ->visible(fn (callable $get) => ($get('type') === 'barangay_clearance')),
                    TextInput::make('custom_fields.address')
                        ->label('Address (Purok, Barangay, City/Municipality)')
                        ->visible(fn (callable $get) => ($get('type') === 'barangay_clearance'))
                        ->required(),
                        TextInput::make('custom_fields.purpose')
                        ->label('Purpose')
                        ->visible(fn (callable $get) => ($get('type') === 'barangay_clearance'))
                        ->required()
                        ->afterStateUpdated(fn ($set, $state) => $set('custom_fields.purpose', strtoupper($state))), 
                        TextInput::make('custom_fields.control_number')
                        ->label('Control Number')
                        // ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                        ->required(),                   
                ])->columns(2),

                Section::make('Person Incase of Emergency')->schema([
                    TextInput::make('custom_fields.emergency_name')
                        ->label('Name')
                        ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                        ->required(),
                    TextInput::make('custom_fields.emergency_relation')
                        ->label('Relation')
                        ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                        ->required(),
                    TextInput::make('custom_fields.emergency_address')
                        ->label('Address (Purok, Barangay, City/Municipality)')
                        ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                        ->required(),
                    TextInput::make('custom_fields.emergency_contact_number')
                        ->label('Contact Number')
                        ->tel()->telRegex('/^(0|63)\d{10}$/')
                        ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                        ->required(),
                ])
                    ->columns(2)
                    ->visible(fn (callable $get) => ($get('type') === 'barangay_id')),

                Section::make('Authorized Personnel')->schema([

                    Select::make('temp_auth_personnel')
                        ->label('Select Personnel')
                        ->options(\App\Models\Personnel::all()->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $personnel = \App\Models\Personnel::find($state);
                                $set('auth_name_display', $personnel?->name ?? '');
                                $set('auth_position_display', $personnel?->position ?? '');
                            } else {
                                $set('auth_name_display', '');
                                $set('auth_position_display', '');
                            }
                        })
                        ->afterStateHydrated(function (callable $set, callable $get) {
                            $personnelId = $get('temp_auth_personnel');
                            if ($personnelId) {
                                $personnel = \App\Models\Personnel::find($personnelId);
                                $set('auth_name_display', $personnel?->name ?? '');
                                $set('auth_position_display', $personnel?->position ?? '');
                            }
                        })
                        ->nullable()
                        ->visible(fn (callable $get) => $get('is_punong_barangay_not_available'))
                        ->required(fn (callable $get) => $get('is_punong_barangay_not_available')),

                    TextInput::make('auth_name_display')
                        ->label('Authorized Name')
                        ->hidden()
                        ->disabled()
                        ->dehydrated(false) // Do NOT store this in the database
                        ->visible(fn (callable $get) => $get('is_punong_barangay_not_available')),

                    Select::make('auth_position_display')
                        ->label('Authorized Position')
                        ->disabled()
                        ->dehydrated(false) // Do NOT store this in the database
                        ->options([
                            'Barangay Kapitan' => 'Barangay Kapitan',
                            'Barangay Kagawad' => 'Barangay Kagawad',
                            'SK Chairman' => 'SK Chairman',
                            'IPMR' => 'IPMR',
                        ])
                        ->visible(fn (callable $get) => $get('is_punong_barangay_not_available')),


                ])
                        ->columns(2)
                        ->visible(fn (callable $get) => $get('is_punong_barangay_not_available')),
            ])
                ->columnSpan(2),

            Group::make()->schema([
                Section::make()->schema([
                    Select::make('type')
                        ->required()
                        ->disabledOn('edit')
                        ->reactive()
                        ->options([
                            'barangay_id' => 'Barangay ID',
                            'barangay_clearance' => 'Barangay Clearance',
                        ]),
                    Select::make('status')
                        ->required()
                        ->default('pending')
                        ->options([
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord && (auth()->user()->isAdmin() || auth()->user()->isClerk())),
                        Toggle::make('is_punong_barangay_not_available')
                        ->visible(fn (callable $get) => ($get('type') === 'barangay_clearance'))
                        ->label('Is Punong Barangay Not Available?')
                        ->onIcon('heroicon-m-check-badge')
                        ->offIcon('heroicon-m-x-circle')
                        ->reactive(),
                        Toggle::make('custom_fields.e_sign')
                            ->visible(fn (callable $get) => ($get('type') === 'barangay_id'))
                            ->label('Attach an E-Signature?')
                            ->onIcon('heroicon-m-check-badge')
                            ->offIcon('heroicon-m-x-circle')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    // Fetch the chairman's ID from the personnels table
                                    $chairmanId = \App\Models\Personnel::where('position', 'chairman')->value('id');
                                    
                                    // Set it to the temp_auth_personnel column directly (not inside custom_fields)
                                    $set('temp_auth_personnel', $chairmanId);
                                } else {
                                    // If unchecked, you may want to clear it
                                    $set('temp_auth_personnel', null);
                                }
                            }),

                ]),
            ]),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Generated/Requested by')
                    ->searchable()
                    ->visible(auth()->user()->isAdmin() || auth()->user()->isClerk()),
                TextColumn::make('type')->label('Type')->formatStateUsing(fn (string $state): string => match ($state) {
                    'barangay_id' => 'Barangay ID',
                    'barangay_clearance' => 'Barangay Clearance',
                }),
                TextColumn::make('status')->label('Status')->formatStateUsing(fn (string $state): string => match ($state) {
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                })->badge()->color(fn (string $state): string => match ($state) {
                    'pending' => 'gray',
                    'approved' => 'success',
                    'rejected' => 'danger',
                })
                    ->searchable(),
                TextColumn::make('created_at')->label('Date Requested')->date('F d, Y h:i A')->timezone('Asia/Manila')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Filter by User')
                    ->searchable()
                    ->visible(auth()->user()->isAdmin() || auth()->user()->isClerk())
                    ->options(User::query()->where('role', 'consumers')->whereNot('id', auth()->user()->id)->get()->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(function ($record) {
                        $user = auth()->user();

                        if ($user->isConsumer() && $record->status === 'pending') {
                            return true;
                        } else {

                            if ($user->isAdmin() || $user->isClerk()) {
                                return true;
                            }

                            return false;
                        }
                    }),
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalContent(function ($record): View {
                        return view('filament.pages.display-pdf', [
                            'file' => $record,
                        ]);
                    })
                    ->modalCancelAction(false)
                    ->modalSubmitAction(false)
                    ->visible(function ($record) {
                        $user = auth()->user();

                        if (($user->isAdmin() || $user->isClerk()) && $record->file_path !== null) {
                            return true;
                        }

                        return false;
                    })
                    ->modalWidth('full'),
                Tables\Actions\DeleteAction::make()
                    ->visible(function ($record) {
                        $user = auth()->user();

                        if ($user->isConsumer() && $record->status === 'pending') {
                            return true;
                        } else {

                            if ($user->isAdmin() || $user->isClerk()) {
                                return true;
                            }

                            return false;
                        }
                    })
                    ->action(function ($record) {
                        if ($record->file_path && Storage::disk('public')->exists($record->file_path)) {
                            Storage::disk('public')->delete($record->file_path);
                        }

                        $record->delete();

                        Notification::make()
                            ->title('Success')
                            ->icon('heroicon-o-check')
                            ->body('The request document has been deleted.')
                            ->send();
                    }),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return auth()->user()->isAdmin() || auth()->user()->isClerk() ? $query->latest() : $query->where('user_id', auth()->user()->id)->latest();
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
            'index' => Pages\ListRequestDocuments::route('/'),
            'create' => Pages\CreateRequestDocument::route('/create'),
            'edit' => Pages\EditRequestDocument::route('/{record}/edit'),
        ];
    }

    
}
