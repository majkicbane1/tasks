<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CompletesLogin;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use CompletesLogin;

    public function showLogin()
    {
        return view('auth.login', [
            'googleEnabled' => Setting::googleEnabled(),
            'registrationOpen' => Setting::bool('auth.registration_enabled', true),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = 'login:'.Str::lower($credentials['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);

            return back()->withErrors(['email' => "Previše pokušaja. Pokušaj ponovo za {$minutes} min."])->onlyInput('email');
        }

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($key, 300);

            return back()->withErrors(['email' => 'Uneti podaci nisu ispravni.'])->onlyInput('email');
        }

        RateLimiter::clear($key);

        return $this->finishLogin($request, $user, $request->boolean('remember'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
