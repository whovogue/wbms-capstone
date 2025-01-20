<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\ResetPasswordServices;
use Closure;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;

class ResetPassword extends RequestPasswordReset
{
    protected static string $view = 'filament.pages.reset-password';

    public $wantToReset;

    public $decryptedEmail;

    public function mount(): void
    {
        $this->wantToReset = request()->enableReset;

        if ($this->wantToReset) {
            $this->decryptedEmail = Crypt::decrypt($this->wantToReset);

            now()->gte($this->decryptedEmail['expiration'])
                ? redirect('/')
                : $this->form->fill(['email' => $this->decryptedEmail['email']]);
        }
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->required(),
                        TextInput::make('new_password')
                            ->reactive()
                            ->required()
                            ->minLength(8)
                            ->maxLength(255)
                            ->password()
                            ->rules([
                                Password::min(8) // Minimum length of 8 characters
                                    ->mixedCase(), // Requires uppercase and lowercase letters
                                'regex:/^(?=(.*\d){4,}).*$/', // Custom rule: At least 4 numeric characters
                                'regex:/[!@#$%^&*(),.?":{}|<>]/', // Custom rule: At least one special character
                            ])
                            ->validationMessages([
                                'regex' => 'The password must include at least 4 numeric characters and one special character.',
                            ])
                            ->revealable()
                            ->hidden(! $this->wantToReset),
                        TextInput::make('confirm_password')
                            ->required()
                            ->password()
                            ->rules([
                                Password::min(8) // Minimum length of 8 characters
                                    ->mixedCase(), // Requires uppercase and lowercase letters
                                'regex:/^(?=(.*\d){4,}).*$/', // Custom rule: At least 4 numeric characters
                                'regex:/[!@#$%^&*(),.?":{}|<>]/', // Custom rule: At least one special character
                            ])
                            ->validationMessages([
                                'regex' => 'The password must include at least 4 numeric characters and one special character.',
                            ])
                            ->revealable()
                            ->rules([
                                function (callable $get) {
                                    return function (string $attribute, $value, Closure $fail) use ($get) {
                                        if ($get('new_password') !== $value) {
                                            $fail('Password not match');
                                        }
                                    };
                                },
                            ])
                            ->hidden(! $this->wantToReset),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    public function request(): void
    {

        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return;
        }
        $record = User::where('email', $this->form->getState()['email'])->first();

        (new ResetPasswordServices)->handle($record);
    }

    public function update()
    {
        $decryptedEmail = Crypt::decrypt($this->wantToReset);

        $user = User::where('email', $decryptedEmail['email'])->first();

        $user->update([
            'password' => Hash::make($this->form->getState()['confirm_password']),
        ]);

        Redirect::to('/');
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label('Back to login')
            ->icon('heroicon-m-arrow-left')
            ->url(filament()->getLoginUrl());
    }

    protected function getRequestFormAction(): Action
    {
        return Action::make('request')
            ->label(! $this->wantToReset ? 'Send Email' : 'Update Password')
            ->action(! $this->decryptedEmail ? 'request' : 'update');
    }

    public function getWidgetData()
    {
        return [];
    }

    public function getCachedSubNavigation()
    {
        return [];
    }

    public function getHeader()
    {
        return [];
    }

    public function getFooter()
    {
        return [];
    }

    public function getCachedHeaderActions()
    {
        return [];
    }

    public function getBreadCrumbs()
    {
        return [];
    }

    public function getVisibleHeaderWidgets()
    {
        return [];
    }

    public function getVisibleFooterWidgets()
    {
        return [];
    }
}
