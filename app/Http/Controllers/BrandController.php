<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantBranding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    /** Own firm: name and branding, edited by the firm's admin. */
    public function edit(Request $request)
    {
        abort_unless($request->user()->tenant, 404);

        return view('firm.edit', ['tenant' => $request->user()->tenant]);
    }

    public function update(Request $request)
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 404);

        $request->validate(TenantBranding::rules());
        TenantBranding::apply($tenant, $request);

        return back()->with('status', 'Podaci firme su sačuvani.');
    }

    /** Public, so the logo also shows on pages the visitor is not signed in to. */
    public function asset(Tenant $tenant, string $slot)
    {
        abort_unless(array_key_exists($slot, Tenant::BRANDING), 404);

        $path = $tenant->{$slot};
        abort_unless($path && Storage::disk(TenantBranding::DISK)->exists($path), 404);

        // an uploaded SVG must never run scripts if someone opens the URL directly
        return Storage::disk(TenantBranding::DISK)->response($path, null, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
