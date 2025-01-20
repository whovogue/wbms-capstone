<?php

namespace App\Livewire;

use App\Mail\TwoFactorMail;
use App\Models\UserCodes;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use HasanAhani\FilamentOtpInput\Components\OtpInput;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Session;

class TwoFactor extends Component implements HasForms
{
    use InteractsWithForms, WithRateLimiting;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function render(): View
    {
        return view('livewire.two-factor');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                OtpInput::make('code')
                    ->numberInput(6)
                    ->label(''),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        try {
            $this->rateLimit(5);

            $find = UserCodes::where('user_id', auth()->user()->id)
                ->where('code', $this->form->getState()['code'])
                ->where('updated_at', '>=', now()->subMinutes(2))
                ->first();

            if (! is_null($find)) {
                Session::put('user_2fa', auth()->user()->id);
                redirect()->intended(Filament::getUrl());
            } else {
                Notification::make()
                    ->title('Expired or Invalid Code')
                    ->danger()
                    ->send();
            }
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title('Too many attempts!!')
                ->danger()
                ->send();
        }
    }

    public function resend()
    {
        try {
            $this->rateLimit(5);
            $this->form->fill([]);

            $code = rand(100000, 999999);

            UserCodes::updateOrCreate(
                ['user_id' => auth()->user()->id],
                ['code' => $code]
            );

            try {
                $details = [
                    'title' => 'Mail from NetlinkVoice.com',
                    'code' => $code,
                    'name' => auth()->user()->name,
                ];

                Mail::to(auth()->user()->email)->send(new TwoFactorMail($details));

                Notification::make()
                    ->title('Code sent successfully to your email')
                    ->success()
                    ->send();
            } catch (Exception $e) {
                logger('Error: '.$e->getMessage());
            }
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title('Too many attempts!!')
                ->danger()
                ->send();
        }
    }
}
