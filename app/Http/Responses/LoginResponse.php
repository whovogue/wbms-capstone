<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse extends \Filament\Http\Responses\Auth\LoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = Auth::user();

        // If the user is a reader, redirect to '/app'
        if ($user && $user->role === 'reader') {
            return redirect('app');
        }
        // If the user is a Book Keeper, redirect to '/app'
        if ($user && $user->role === 'admin') {
            return redirect('app');
        }
                        // If the user is a Clerk, redirect to '/app'
        if ($user && $user->role === 'clerk') {
            return redirect('app');
        }

        // For other users, redirect to 2FA
        if ($user) {
            $user->generateCode();
            return redirect()->route('2fa.index');
        }

        return parent::toResponse($request);
    }
}
