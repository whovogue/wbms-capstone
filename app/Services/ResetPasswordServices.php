<?php

namespace App\Services;

use App\Mail\PasswordReset;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;

class ResetPasswordServices
{
    public function handle($record)
    {

        if ($record && ! $record->is_deactivated) {
            $encryptedEmail = Crypt::encrypt([
                'email' => $record->email,
                'expiration' => now()->addMinutes(2),
            ]);

            Mail::to($record->email)->send(new PasswordReset($record, $encryptedEmail));
        }

        Notification::make()
            ->title('Password reset request sent')
            ->body('Check your email for you to change your password')
            ->success()
            ->seconds(4)
            ->send();

        Redirect::to('/');
    }
}
