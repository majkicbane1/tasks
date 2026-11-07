<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // the platform owner has no clients of their own: send them to the firms overview instead of a bare 403
        if ($request->user()?->isPlatformAdmin()) {
            return redirect()->route('platform.tenants.index');
        }

        abort_unless($request->user()?->isSuperAdmin(), 403);

        return $next($request);
    }
}
