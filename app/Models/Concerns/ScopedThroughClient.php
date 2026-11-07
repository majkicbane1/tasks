<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;

/** Row owned by a firm indirectly: projects, payments (via client) and work entries (via project). */
trait ScopedThroughClient
{
    public static function bootScopedThroughClient(): void
    {
        static::addGlobalScope(new TenantScope());
    }
}
