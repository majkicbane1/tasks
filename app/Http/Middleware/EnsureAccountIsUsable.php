<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs for every signed in request:
 *  - kicks out users of a deactivated firm,
 *  - when the platform requires verified emails, sends unverified users to the verification screen.
 */
class EnsureAccountIsUsable
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->tenant && ! $user->tenant->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Nalog ove firme je deaktiviran.']);
        }

        if ($user->must_change_password && ! $request->routeIs('password.force*', 'logout')) {
            return redirect()->route('password.force');
        }

        if (Setting::bool('auth.require_verification')
            && ! $user->isPlatformAdmin()
            && ! $user->hasVerifiedEmail()
            && ! $request->routeIs('verification.*', 'logout')) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
