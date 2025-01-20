<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Session;

class LogoutResponse implements Responsable
{
    public function toResponse($request): RedirectResponse
    {
        Session::flush();

        // change this to your desired route
        return redirect('/');
    }
}
