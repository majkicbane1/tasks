<?php

namespace App\Http\Controllers;

use App\Services\Totp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** Own profile for every role: name, password and Google Authenticator 2FA. */
class AccountController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $settingUp = $user->two_factor_secret && ! $user->hasTwoFactor();

        return view('account.edit', [
            'user' => $user,
            'settingUp' => $settingUp,
            'secret' => $settingUp ? $user->two_factor_secret : null,
            'otpauth' => $settingUp ? Totp::uri($user->two_factor_secret, $user->email, 'Uizzard') : null,
        ]);
    }

    /** Shown right after signing in with a one-time password. */
    public function forcePasswordForm(Request $request)
    {
        return $request->user()->must_change_password
            ? view('auth.force-password')
            : redirect()->route($request->user()->homeRoute());
    }

    public function forcePasswordUpdate(Request $request)
    {
        abort_unless($request->user()->must_change_password, 403);

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'password.different' => 'Nova lozinka mora da se razlikuje od jednokratne.',
        ]);

        $request->user()->forceFill(['password' => $data['password'], 'must_change_password' => false])->save();

        return redirect()->route($request->user()->homeRoute())->with('status', 'Lozinka je postavljena.');
    }

    public function update(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);
        $request->user()->update($data);

        return back()->with('status', 'Profil je sačuvan.');
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update(['password' => $data['password']]);

        return back()->with('status', 'Lozinka je promenjena.');
    }

    /** Step 1: generate a secret and show the QR code. 2FA is not active until the first code is confirmed. */
    public function startTwoFactor(Request $request)
    {
        $user = $request->user();
        abort_if($user->hasTwoFactor(), 422);

        $user->forceFill([
            'two_factor_secret' => Totp::generateSecret(),
            'two_factor_confirmed_at' => null,
            'two_factor_last_step' => null,
        ])->save();

        return redirect()->route('account.edit');
    }

    /** Step 2: prove the authenticator app works, then turn 2FA on and hand out recovery codes. */
    public function confirmTwoFactor(Request $request)
    {
        $user = $request->user();
        abort_unless($user->two_factor_secret && ! $user->hasTwoFactor(), 422);

        $data = $request->validate(['code' => ['required', 'string', 'max:10']]);

        $step = Totp::verify($user->two_factor_secret, $data['code']);
        if ($step === null) {
            return back()->withErrors(['code' => 'Kod nije ispravan. Proveri da je vreme na telefonu tačno.']);
        }

        $codes = $this->newRecoveryCodes();
        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_last_step' => $step,
            'two_factor_recovery_codes' => $codes,
        ])->save();

        return redirect()->route('account.edit')->with('status', '2FA je uključen.')->with('recovery_codes', $codes);
    }

    public function recoveryCodes(Request $request)
    {
        abort_unless($request->user()->hasTwoFactor(), 422);
        if ($error = $this->rejectCode($request)) {
            return $error;
        }

        $codes = $this->newRecoveryCodes();
        $request->user()->forceFill(['two_factor_recovery_codes' => $codes])->save();

        return redirect()->route('account.edit')->with('status', 'Novi rezervni kodovi su napravljeni.')->with('recovery_codes', $codes);
    }

    public function disableTwoFactor(Request $request)
    {
        if ($error = $this->rejectCode($request)) {
            return $error;
        }

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_last_step' => null,
        ])->save();

        return redirect()->route('account.edit')->with('status', '2FA je isključen.');
    }

    /** Sensitive 2FA changes need a fresh code from the authenticator app (works for Google-only accounts too). */
    private function rejectCode(Request $request)
    {
        $user = $request->user();
        $code = (string) $request->validate(['code' => ['required', 'string', 'max:10']])['code'];

        $step = $user->two_factor_secret ? Totp::verify($user->two_factor_secret, $code, $user->two_factor_last_step) : null;
        if ($step === null) {
            return back()->withErrors(['code' => 'Kod nije ispravan.']);
        }
        $user->forceFill(['two_factor_last_step' => $step])->save();

        return null;
    }

    private function newRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => strtolower(Str::random(5).'-'.Str::random(5)))
            ->all();
    }
}
