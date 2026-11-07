<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreatePlatformAdmin extends Command
{
    protected $signature = 'platform:admin {email} {--name=Platform Admin} {--password= : Leave out to be asked}';

    protected $description = 'Create (or promote / reset) the platform admin that manages all firms';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->option('password') ?: $this->secret('Lozinka (min 8 karaktera)');

        if (strlen((string) $password) < 8) {
            $this->error('Lozinka mora da ima bar 8 karaktera.');

            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->forceFill([
            'role' => User::ROLE_PLATFORM,
            'tenant_id' => null,
            'client_id' => null,
            'name' => $user->exists ? $user->name : $this->option('name'),
            'password' => Hash::make($password),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $this->info("Platform admin je spreman: {$email}");

        return self::SUCCESS;
    }
}
