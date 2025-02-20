<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Management';

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin();
        // return auth()->user()->isAdmin() || auth()->user()->isClerk();
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::count();
        return $count > 0 ? (string) $count : null;
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
    protected static ?string $navigationBadgeTooltip = 'Number of Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')
                        ->required(),
                    TextInput::make('email')
                        ->required()->email()
                        ->unique(ignoreRecord: true),
                    TextInput::make('password')
                        ->rules([
                            Password::min(8) // Minimum length of 8 characters
                                ->mixedCase(), // Requires uppercase and lowercase letters
                            'regex:/^(?=(.*\d){4,}).*$/', // Custom rule: At least 4 numeric characters
                            'regex:/[!@#$%^&*(),.?":{}|<>]/', // Custom rule: At least one special character
                        ])
                        ->validationMessages([
                            'regex' => 'The password must include at least 4 numeric characters and one special character.',
                        ])
                        ->minLength(8)
                        ->maxLength(255)
                        ->password()->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                        ->revealable(),
                    Select::make('role')
                        ->required()
                        ->options([
                            'admin' => 'Admin',
                            'clerk' => 'Clerk',
                            'consumers' => 'Consumers',
                            'reader' => 'Meter Reader',
                        ]),
                    DatePicker::make('date_of_birth')
                        ->maxDate(now())
                        ->required(),
                    TextInput::make('birthplace')
                        ->required(),
                    TextInput::make('address'),
                    Select::make('purok')
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
                        ->label('Purok')
                        ->required(),
                    TextInput::make('citizenship')
                        ->required(),
                    TextInput::make('religion')
                        ->required(),
                    Select::make('gender')
                        ->label('Gender')
                        ->options([
                            'Male' => 'Male',
                            'Female' => 'Female',
                            'Prefer' => 'Prefer not to say',
                        ])
                        ->required(),
                    TextInput::make('contact_number')
                        ->tel()->telRegex('/^(0|63)\d{10}$/')
                        ->required(),
                    Select::make('civil_status')
                        ->label('Civil Status')
                        ->options([
                            'single' => 'Single',
                            'married' => 'Married',
                            'widowed' => 'Widowed',
                            'divorced' => 'Divorced',
                        ])
                        ->required(),
                        // ADDED TERMS AND AGREEMENT
                        Checkbox::make('terms_agreement')
                        ->label('Agreed to Terms & Conditions')
                        ->default(true) // Ensures it defaults to true when creating a new user
                        ->disabled(fn ($record) => $record !== null) // Disables the checkbox when editing (non-editable)
                        ->hidden(fn ($record) => $record !== null) // Hides the checkbox when editing an existing user
                        ->required(),
                        TextInput::make('profile_photo_path')
                        ->default('avatars/default_profile.png')
                        ->dehydrated(fn ($state) => $state ?: 'avatars/default_profile.png') // Ensure it's set if not provided
                        ->hidden(),
                    
                ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('role')->badge()->color(fn (string $state): string => match ($state) {
                    'admin' => 'success',
                    'consumers' => 'gray',
                    'reader' => 'warning',
                    'clerk' => 'info',
                })->formatStateUsing(fn (string $state): string => __(ucfirst($state))),
                TextColumn::make('created_at')->label('Date Created')->date('F d, Y h:i A')->timezone('Asia/Manila'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('purok')
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
                    Tables\Filters\SelectFilter::make('role')
                    ->label('Filter by Role')
                    ->options(fn () => User::query()
                        ->select('role')
                        ->distinct()
                        ->pluck('role', 'role') // Fetch unique roles
                        ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                ->requiresConfirmation() // This adds a confirmation prompt
                ->label('Delete') // Optional: Customize the action label if needed
                ->icon('heroicon-o-trash'), // Optional: Set the icon for the action
            ])
            ->groups([
                Tables\Grouping\Group::make('role')
                    ->label('User Role')
                    ->collapsible(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
