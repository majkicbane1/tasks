<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CompletesLogin;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/** "Sign in with Google" via the OAuth 2.0 authorization code flow. Keys are set by the platform admin. */
class GoogleAuthController extends Controller
{
    use CompletesLogin;

    public function redirect(Request $request)
    {
        abort_unless(Setting::googleEnabled(), 404);

        $state = Str::random(40);
        $request->session()->put('google_state', $state);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => route('google.callback'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ]));
    }

    public function callback(Request $request)
    {
        abort_unless(Setting::googleEnabled(), 404);

        $state = $request->session()->pull('google_state');
        if (! $state || ! hash_equals($state, (string) $request->query('state')) || ! $request->query('code')) {
            return $this->fail('Prijava preko Google-a nije uspela. Pokušaj ponovo.');
        }

        try {
            $token = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $request->query('code'),
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => route('google.callback'),
                'grant_type' => 'authorization_code',
            ])->throw()->json('access_token');

            $profile = Http::withToken($token)->get('https://openidconnect.googleapis.com/v1/userinfo')->throw()->json();
        } catch (Throwable $e) {
            report($e);

            return $this->fail('Google nije potvrdio prijavu. Proveri Client ID / Secret i redirect adresu.');
        }

        if (empty($profile['sub']) || empty($profile['email']) || ! ($profile['email_verified'] ?? false)) {
            return $this->fail('Google nalog nema potvrđenu email adresu.');
        }

        $user = User::where('google_id', $profile['sub'])->first()
            ?? User::where('email', $profile['email'])->first();

        if ($user) {
            // someone may have registered this address with a password before ever proving they own it;
            // linking Google makes the owner the only one who can sign in, so drop that password
            if (! $user->hasVerifiedEmail()) {
                $user->password = Hash::make(Str::random(40));
            }
            $user->google_id ??= $profile['sub'];
            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }
            $user->save();
        } else {
            if (! Setting::bool('auth.registration_enabled', true)) {
                return $this->fail('Registracija je trenutno zatvorena.');
            }

            $user = DB::transaction(function () use ($profile) {
                $name = $profile['name'] ?? Str::before($profile['email'], '@');
                $tenant = Tenant::create(['name' => $name]);

                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'role' => User::ROLE_ADMIN,
                    'name' => $name,
                    'email' => $profile['email'],
                    'password' => Hash::make(Str::random(40)),
                    'google_id' => $profile['sub'],
                ]);
                $user->markEmailAsVerified();

                return $user;
            });
        }

        return $this->finishLogin($request, $user);
    }

    private function fail(string $message)
    {
        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
