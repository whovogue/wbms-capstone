<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Profile extends Page implements HasForms
{

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static string $view = 'filament.pages.profile';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 11;

    // protected static ?bool $navigation = false;

    public ?array $data = [];

    public ?array $profileData = [];

    public $user;

    // PARA RA NI MATAGO ANG PROFILE SETTINGS HEHE

    public static function canAccess(): bool
    {
        return false;
    }

    public function mount()
    {
        $this->user = auth()->user();

        $auth = auth()->user();

        $this->getProfileDataFormSchema->fill([
            'email' => $auth->email,
            'date_of_birth' => $auth->date_of_birth,
            'birthplace' => $auth->birthplace,
            'citizenship' => $auth->citizenship,
            'religion' => $auth->religion,
            'gender' => $auth->gender,
            'purok' => $auth->purok,
            'contact_number' => $auth->contact_number,
            'civil_status' => $auth->civil_status,
        ]);

        $this->getProfilePhotoFormSchema->fill([
            'profile_photo_path' => $auth->profile_photo_path,
        ]);
    }

    protected function getForms(): array
    {
        return [
            'getProfilePhotoFormSchema',
            'getProfileDataFormSchema',
        ];
    }

    public function getProfilePhotoFormSchema(Form $form): Form
    {
        return $form->schema([
            FileUpload::make('profile_photo_path')
                ->image()
                ->hiddenLabel()
                ->avatar()
                ->maxSize(1024)
                ->directory('/')                // Files will go directly into public/profile-photos
                ->disk('public_uploads')        // Custom disk for public storage
                ->rules(['nullable', 'mimes:jpg,jpeg,png', 'max:1024']),
        ])
            ->statePath('profileData');
    }

    public function getProfileDataFormSchema(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([

                        TextInput::make('email')
                            ->required(),
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
                        DatePicker::make('date_of_birth')
                            ->required()
                            ->maxDate(now())
                            ->label('Date of Birth'),
                        TextInput::make('birthplace')
                            ->required()
                            ->label('Birthplace'),
                        TextInput::make('citizenship')
                            ->required()
                            ->label('Citizenship'),
                        TextInput::make('religion')
                            ->required()
                            ->label('Religion'),
                        Select::make('gender')
                            ->required()
                            ->label('Gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'prefer' => 'Prefer not to say',
                            ]),
                        Select::make('purok')
                            ->required()
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
                            ->label('Purok'),
                        TextInput::make('contact_number')
                            ->required()
                            ->tel()->telRegex('/^(0|63)\d{10}$/')
                            ->label('Contact Number'),
                        Select::make('civil_status')
                            ->label('Civil Status')
                            ->options([
                                'single' => 'Single',
                                'married' => 'Married',
                                'widowed' => 'Widowed',
                                'divorced' => 'Divorced',
                            ]),
                    ])->columns(2),
            ])
            ->statePath('data');
    }
    public function submit()
    {
        $data = array_merge($this->getProfileDataFormSchema->getState(), $this->getProfilePhotoFormSchema->getState());

        if (file_exists(public_path('profile-photos/'.auth()->user()->profile_photo_path))) {
            File::delete(public_path('profile-photos/'.auth()->user()->profile_photo_path));
        }

        $this->user->update($data);

        session()->put([
            'password_hash_'.auth()->getDefaultDriver() => $this->user->getAuthPassword(),
        ]);

        Notification::make()
            ->title('Updated')
            ->success()
            ->send();
    }
}
