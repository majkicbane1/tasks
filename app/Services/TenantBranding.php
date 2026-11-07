<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Stores, replaces and removes a firm's logos (light / dark) and favicon. */
class TenantBranding
{
    public const DISK = 'local';

    public static function rules(): array
    {
        $file = ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg,ico', 'max:1024'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'logo_light' => $file,
            'logo_dark' => $file,
            'favicon' => $file,
            'remove' => ['nullable', 'array'],
            'remove.*' => ['in:logo_light,logo_dark,favicon'],
        ];
    }

    /** Applies the name plus uploaded / removed files from a validated request. */
    public static function apply(Tenant $tenant, Request $request, array $extra = []): void
    {
        $changes = ['name' => $request->input('name')] + $extra;

        foreach (Tenant::BRANDING as $slot => $column) {
            $remove = in_array($slot, (array) $request->input('remove', []), true);

            if ($request->hasFile($slot)) {
                self::forget($tenant->{$column});
                $changes[$column] = $request->file($slot)->store("brand/{$tenant->id}", self::DISK);
            } elseif ($remove) {
                self::forget($tenant->{$column});
                $changes[$column] = null;
            }
        }

        $tenant->update($changes);
    }

    public static function forget(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
