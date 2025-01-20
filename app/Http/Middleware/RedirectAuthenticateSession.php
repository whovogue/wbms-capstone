<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use Symfony\Component\HttpFoundation\Response;

class RedirectAuthenticateSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth = auth()->user();

        if ($auth->userCodes()) {
            $time = Carbon::parse($auth->userCodes()->first()->updated_at);

            $now = now();

            $diffInMinutes = $time->diffInMinutes($now);

            if ($diffInMinutes >= 2) {

                Auth::logout();

                return redirect('/');
            }
        }

        if (! Session::has('user_2fa')) {
            if (! Auth::check()) {

                return redirect('/app/login');
            }
        }

        if (Session::has('user_2fa')) {
            return redirect('/');
        }

        return $next($request);
    }
}
