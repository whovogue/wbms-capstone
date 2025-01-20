<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect(string $provider)
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        $this->validateProvider($provider);

        $response = Socialite::driver($provider)->user();

        $user = User::firstWhere(['email' => $response->getEmail()]);

        if ($user) {
            if ($user->status == 'inactive') {
                auth()->guard()->logout();
            }

            $user->update([$provider.'_id' => $response->getId()]);
        } else {
            $user = User::create([
                $provider.'_id' => $response->getId(),
                'name' => $response->getName(),
                'email' => $response->getEmail(),
                'role' => 'client',
                'password' => 'password',
            ]);
        }

        auth()->guard()->login($user);

        auth()->user()->generateCode();

        return redirect('/app');
    }

    protected function validateProvider(string $provider): array
    {
        return Validator::make(
            ['provider' => $provider],
            ['provider' => 'in:google']
        )->validate();
    }
}
