<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;

/** Row owned directly by a firm (clients, leave days). */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            $model->tenant_id ??= TenantScope::currentId();
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
