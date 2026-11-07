<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class PasswordResetController extends Controller
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        try {
            Password::sendResetLink($data);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['email' => 'Slanje mejla nije uspelo. Proveri podešavanja mejla ili probaj kasnije.'])->onlyInput('email');
        }

        // same answer whether or not the address exists, so accounts cannot be enumerated
        return back()->with('status', 'Ako nalog postoji, poslali smo link za promenu lozinke.');
    }

    public function form(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset($data, function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ]);
            // following the mailed link proves the address belongs to them
            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }
            $user->save();

            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Lozinka je promenjena. Možeš da se prijaviš.')
            : back()->withErrors(['email' => 'Link je neispravan ili je istekao.'])->onlyInput('email');
    }
}
