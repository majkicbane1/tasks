<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

/** Mail (SMTP), Google login keys and sign-up rules. Only the platform admin can change them. */
class PlatformSettingsController extends Controller
{
    public function index()
    {
        return view('platform.settings', [
            's' => [
                'mailer' => Setting::get('mail.mailer', 'log'),
                'host' => Setting::get('mail.host'),
                'port' => Setting::get('mail.port', 587),
                'encryption' => Setting::get('mail.encryption', 'tls'),
                'username' => Setting::get('mail.username'),
                'has_password' => (bool) Setting::get('mail.password'),
                'from_address' => Setting::get('mail.from_address'),
                'from_name' => Setting::get('mail.from_name', config('app.name')),
                'google_enabled' => Setting::bool('google.enabled'),
                'google_client_id' => Setting::get('google.client_id'),
                'has_google_secret' => (bool) Setting::get('google.client_secret'),
                'registration' => Setting::bool('auth.registration_enabled', true),
                'verification' => Setting::bool('auth.require_verification'),
            ],
            'googleRedirect' => route('google.callback'),
        ]);
    }

    public function updateMail(Request $request)
    {
        $data = $request->validate([
            'mailer' => ['required', 'in:smtp,log'],
            'host' => ['required_if:mailer,smtp', 'nullable', 'string', 'max:255'],
            'port' => ['required_if:mailer,smtp', 'nullable', 'integer', 'between:1,65535'],
            'encryption' => ['nullable', 'in:tls,ssl'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'from_address' => ['required_if:mailer,smtp', 'nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
        ]);

        foreach (['mailer', 'host', 'port', 'encryption', 'username', 'from_address', 'from_name'] as $field) {
            Setting::put("mail.{$field}", $data[$field] ?? null);
        }
        // an empty password box keeps the stored one
        if (! empty($data['password'])) {
            Setting::put('mail.password', $data['password']);
        }
        if ($request->boolean('clear_password')) {
            Setting::put('mail.password', null);
        }

        return redirect()->route('platform.settings')->with('status', 'Podešavanja mejla su sačuvana.');
    }

    public function testMail(Request $request)
    {
        // settings were applied to the config on boot, so this uses what is stored
        try {
            Mail::raw('Ovo je probni mejl iz '.config('app.name').'. Ako ga vidiš, slanje radi.', function ($message) use ($request) {
                $message->to($request->user()->email)->subject('Probni mejl');
            });
        } catch (Throwable $e) {
            return redirect()->route('platform.settings')->withErrors(['mail_test' => 'Slanje nije uspelo: '.$e->getMessage()]);
        }

        return redirect()->route('platform.settings')->with('status', 'Probni mejl je poslat na '.$request->user()->email.'.');
    }

    public function updateGoogle(Request $request)
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'string', 'max:255'],
            'client_secret' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::put('google.client_id', $data['client_id'] ?? null);
        if (! empty($data['client_secret'])) {
            Setting::put('google.client_secret', $data['client_secret']);
        }
        Setting::put('google.enabled', $request->boolean('enabled') ? '1' : '0');

        return redirect()->route('platform.settings')->with('status', 'Google podešavanja su sačuvana.');
    }

    public function updateAuth(Request $request)
    {
        Setting::put('auth.registration_enabled', $request->boolean('registration') ? '1' : '0');
        Setting::put('auth.require_verification', $request->boolean('verification') ? '1' : '0');

        return redirect()->route('platform.settings')->with('status', 'Pravila registracije su sačuvana.');
    }
}
