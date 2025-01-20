<?php

namespace App\Livewire;

use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Events\Auth\Registered;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register as RegisterPage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Register extends RegisterPage
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getDOBFormComponent(),
                        $this->getBirthPlaceFormComponent(),
                        $this->getCitezenhipFormComponent(),
                        $this->getReligionFormComponent(),
                        $this->getGenderFormComponent(),
                        $this->getPurokFormComponent(),
                        $this->getContactNumberFormComponent(),
                        $this->getCivilStatusFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/register.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->rules([
                Password::min(8) // Minimum length of 8 characters
                    ->mixedCase(), // Requires uppercase and lowercase letters
                'regex:/^(?=(.*\d){4,}).*$/', // Custom rule: At least 4 numeric characters
                'regex:/[!@#$%^&*(),.?":{}|<>]/', // Custom rule: At least one special character
            ])
            ->validationMessages([
                'regex' => 'The password must include at least 4 numeric characters and one special character.',
            ])
            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
            ->same('passwordConfirmation')
            ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute'));
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false);
    }

    protected function getDOBFormComponent(): Component
    {
        return DatePicker::make('date_of_birth')
            ->maxDate(now())
            ->label('Date of Birth')
            ->required();
    }

    protected function getBirthPlaceFormComponent(): Component
    {
        return TextInput::make('birthplace')
            ->label('Birthplace')
            ->required();
    }

    protected function getCitezenhipFormComponent(): Component
    {
        return TextInput::make('citizenship')
            ->label('Citizenship')
            ->required();
    }

    protected function getReligionFormComponent(): Component
    {
        return TextInput::make('religion')
            ->label('Religion')
            ->required();
    }

    protected function getGenderFormComponent(): Component
    {
        return Select::make('gender')
            ->label('Gender')
            ->options([
                'male' => 'Male',
                'female' => 'Female',
                'prefer' => 'Prefer not to say',
            ])
            ->required();
    }

    protected function getPurokFormComponent(): Component
    {
        return Select::make('purok')
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
            ->required();
    }

    protected function getContactNumberFormComponent(): Component
    {
        return TextInput::make('contact_number')
            ->tel()->telRegex('/^(0|63)\d{10}$/')
            ->label('Contact Number')
            ->required();
    }

    protected function getCivilStatusFormComponent(): Component
    {
        return Select::make('civil_status')
            ->label('Civil Status')
            ->options([
                'single' => 'Single',
                'married' => 'Married',
                'widowed' => 'Widowed',
                'divorced' => 'Divorced',
            ])
            ->required();
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['role'] = 'consumers';
        $data['consumer_number'] = $this->generateConsumerNumber();

        return $data;
    }

    public function generateConsumerNumber(): string
    {
        do {
            $consumerNumber = random_int(1000, 9999).'-'.random_int(100000, 999999); // Adjusted range for the first part
        } while (User::where('consumer_number', $consumerNumber)->exists());

        return $consumerNumber;
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(10);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function () {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            return $user;
        });

        event(new Registered($user));

        $this->sendEmailVerificationNotification($user);

        Filament::auth()->login($user);

        session()->regenerate();

        auth()->user()->generateCode();

        return app(RegistrationResponse::class);
    }
}
