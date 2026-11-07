<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function show()
    {
        abort_unless(Setting::bool('auth.registration_enabled', true), 404);

        return view('auth.register', ['googleEnabled' => Setting::googleEnabled()]);
    }

    public function store(Request $request)
    {
        abort_unless(Setting::bool('auth.registration_enabled', true), 404);

        $data = $request->validate([
            'tenant_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $tenant = Tenant::create(['name' => $data['tenant_name']]);

            return User::create([
                'tenant_id' => $tenant->id,
                'role' => User::ROLE_ADMIN,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
        });

        Auth::login($user);
        $request->session()->regenerate();

        if (Setting::bool('auth.require_verification')) {
            $user->sendEmailVerificationNotification();

            return redirect()->route('verification.notice');
        }

        return redirect()->route('dashboard')->with('status', 'Dobro došao! Nalog je napravljen.');
    }
}
