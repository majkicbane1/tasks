<?php

namespace App\Support;

use App\Models\Client;
use App\Models\Scopes\TenantScope;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * "exists:" validation talks to the database directly and bypasses Eloquent scopes, so a plain
 * "exists:clients,id" would accept another firm's ids. These rules keep the check inside the firm.
 */
class TenantRule
{
    public static function client(): Exists
    {
        return Rule::exists('clients', 'id')
            ->where(fn ($query) => $query->where('tenant_id', TenantScope::currentId() ?? 0));
    }

    public static function project(): Exists
    {
        return Rule::exists('projects', 'id')
            ->where(fn ($query) => $query->whereIn('client_id', Client::query()->select('clients.id')));
    }
}
