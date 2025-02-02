<?php

namespace App\Livewire;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasSort;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Checkbox;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomProfileComponent extends Component implements HasForms
{
    use InteractsWithForms;
    use HasSort;

    public ?array $data = [];

    protected static int $sort = 15;

    public function mount(): void
    {
        $this->user = auth()->user();

        $auth = auth()->user();

        $this->form->fill([
            // 'email' => $auth->email,
            'date_of_birth' => $auth->date_of_birth,
            'birthplace' => $auth->birthplace,
            'citizenship' => $auth->citizenship,
            'religion' => $auth->religion,
            'gender' => $auth->gender,
            'purok' => $auth->purok,
            'contact_number' => $auth->contact_number,
            'civil_status' => $auth->civil_status,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Profile Information')
                    ->aside()
                    ->description('Update your Profile information')
                    ->schema([
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
                                    'male' => 'Male',
                                    'female' => 'Female',
                                    'prefer' => 'Prefer not to say',
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

                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->validate(); // Ensure data is validated
    
        $auth = auth()->user(); // Get authenticated user
    
        $auth->update($this->form->getState()); // Update the user in the database
    
        Notification::make()
        ->title('Updated')
        ->success()
        ->send();
    }
    

    public function render(): View
    {
        return view('livewire.custom-profile-component');
    }
}
