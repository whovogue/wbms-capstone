<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Check2FA
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // If the user is not authenticated, allow the request to proceed
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Skip 2FA if the user is a reader
        if ($user->role === 'reader') {
            return $next($request);
        }

        // If the session key for 2FA verification does not exist, redirect to the 2FA page
        if (!session()->has('user_2fa')) {
            // Prevent redirection loops by ensuring the current route isn't the 2FA route
            if ($request->route()->getName() !== '2fa.index') {
                return redirect()->route('2fa.index');
            }
        }

        // Allow the request to proceed if the session key exists or user is already on the 2FA page
        return $next($request);
    }
}
