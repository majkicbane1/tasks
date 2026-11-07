<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['tenant_id', 'client_id', 'role', 'name', 'email', 'password', 'google_id', 'email_verified_at', 'must_change_password'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_PLATFORM = 'platform_admin';
    public const ROLE_ADMIN = 'super_admin'; // admin of one firm (tenant)
    public const ROLE_CLIENT = 'client';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'must_change_password' => 'boolean',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class)->withoutGlobalScopes();
    }

    /** Admin of a firm (the "freelancer"): manages that firm's clients, projects, payments. */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Owner of the whole platform: manages firms, mail, Google and security settings. */
    public function isPlatformAdmin(): bool
    {
        return $this->role === self::ROLE_PLATFORM;
    }

    public function hasTwoFactor(): bool
    {
        return $this->two_factor_confirmed_at !== null && $this->two_factor_secret !== null;
    }

    public function homeRoute(): string
    {
        return $this->isPlatformAdmin() ? 'platform.tenants.index' : 'dashboard';
    }
}
