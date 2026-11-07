<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * One-time upgrade screen for the firm that existed before multi-tenancy.
 * Until the tenants migration has run, the old admin cannot reach any other page, so this lets them
 * run it from the browser and create the platform admin. Locked as soon as the migration is done.
 */
class UpgradeController extends Controller
{
    public function show(Request $request)
    {
        $this->authorizeUpgrade($request);

        return view('auth.upgrade');
    }

    public function run(Request $request)
    {
        $this->authorizeUpgrade($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['migrate' => 'Migracija nije uspela: '.$e->getMessage()])->withInput();
        }

        User::create([
            'role' => User::ROLE_PLATFORM,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('status', 'Migracije su izvršene i platform admin je napravljen: '.$data['email']);
    }

    private function authorizeUpgrade(Request $request): void
    {
        abort_unless($request->user()?->isSuperAdmin() && ! Schema::hasTable('tenants'), 404);
    }
}
