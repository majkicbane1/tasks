<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait CompletesLogin
{
    /** Final step of every sign-in method (password, Google): firm check, 2FA hand-off, session start. */
    protected function finishLogin(Request $request, User $user, bool $remember = false)
    {
        if ($user->tenant && ! $user->tenant->is_active) {
            return redirect()->route('login')->withErrors(['email' => 'Nalog ove firme je deaktiviran.']);
        }

        if ($user->hasTwoFactor()) {
            $request->session()->put('two_factor', [
                'id' => $user->id,
                'remember' => $remember,
                'expires' => now()->addMinutes(5)->timestamp,
            ]);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route($user->homeRoute()));
    }
}
