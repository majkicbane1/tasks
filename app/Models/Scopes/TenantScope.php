<?php

namespace App\Models\Scopes;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Limits every query to the firm (tenant) of the logged in user.
 * Not applied for guests (shared links, console) or for the platform admin.
 * Models without their own tenant_id are limited through their client / project.
 */
class TenantScope implements Scope
{
    public static function currentId(): ?int
    {
        $user = auth()->user();

        return $user && ! $user->isPlatformAdmin() ? (int) $user->tenant_id : null;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = self::currentId();
        if ($tenantId === null) {
            return;
        }

        $table = $model->getTable();

        match (true) {
            $table === 'clients' || $table === 'leave_days'
                => $builder->where("{$table}.tenant_id", $tenantId),
            $table === 'projects' || $table === 'payments'
                => $builder->whereIn("{$table}.client_id", Client::query()->select('clients.id')),
            $table === 'work_entries'
                => $builder->whereIn('work_entries.project_id', Project::query()->select('projects.id')),
            default => null,
        };
    }
}
