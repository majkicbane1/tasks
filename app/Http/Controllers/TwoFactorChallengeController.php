<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Totp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

/** Second step of sign-in for accounts that turned on Google Authenticator (TOTP) 2FA. */
class TwoFactorChallengeController extends Controller
{
    public function show(Request $request)
    {
        return $this->pendingUser($request) ? view('auth.two-factor') : redirect()->route('login');
    }

    public function verify(Request $request)
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        $data = $request->validate(['code' => ['required', 'string', 'max:32']]);

        $key = 'two-factor:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $request->session()->forget('two_factor');

            return redirect()->route('login')->withErrors(['email' => 'Previše pogrešnih kodova. Prijavi se ponovo za nekoliko minuta.']);
        }

        if (! $this->codeIsValid($user, $data['code'])) {
            RateLimiter::hit($key, 300);

            return back()->withErrors(['code' => 'Kod nije ispravan.']);
        }

        RateLimiter::clear($key);
        $remember = (bool) $request->session()->pull('two_factor.remember');
        $request->session()->forget('two_factor');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route($user->homeRoute()));
    }

    private function pendingUser(Request $request): ?User
    {
        $pending = $request->session()->get('two_factor');
        if (! $pending || $pending['expires'] < now()->timestamp) {
            $request->session()->forget('two_factor');

            return null;
        }

        $user = User::find($pending['id']);

        return $user?->hasTwoFactor() ? $user : null;
    }

    private function codeIsValid(User $user, string $code): bool
    {
        $step = Totp::verify($user->two_factor_secret, $code, $user->two_factor_last_step);
        if ($step !== null) {
            $user->forceFill(['two_factor_last_step' => $step])->save(); // a code works only once

            return true;
        }

        $normalized = strtolower(trim($code));
        $codes = $user->two_factor_recovery_codes ?? [];
        foreach ($codes as $i => $recovery) {
            if (hash_equals($recovery, $normalized)) {
                unset($codes[$i]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }
}
