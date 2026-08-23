<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isSuperAdmin()) {
            return $next($request);
        }

        $project = $request->route('project');

        if ($project && (int) $project->client_id !== (int) $user?->client_id) {
            abort(403);
        }

        return $next($request);
    }
}
