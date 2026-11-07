<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'is_active', 'logo_light', 'logo_dark', 'favicon'])]
class Tenant extends Model
{
    /** Branding slots: form field => column. */
    public const BRANDING = ['logo_light' => 'logo_light', 'logo_dark' => 'logo_dark', 'favicon' => 'favicon'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function admins()
    {
        return $this->users()->where('role', 'super_admin');
    }

    public function clients()
    {
        return $this->hasMany(Client::class)->withoutGlobalScopes();
    }

    /** URL of an uploaded branding file (light logo, dark logo, favicon) or null. Versioned to bust caches. */
    public function brandUrl(string $slot): ?string
    {
        return $this->{$slot}
            ? route('brand.asset', ['tenant' => $this->id, 'slot' => $slot, 'v' => $this->updated_at?->timestamp])
            : null;
    }

    /** Logo for the given theme; falls back to the other theme's logo when only one was uploaded. */
    public function logoUrl(string $theme): ?string
    {
        $other = $theme === 'dark' ? 'logo_light' : 'logo_dark';

        return $this->brandUrl($theme === 'dark' ? 'logo_dark' : 'logo_light') ?? $this->brandUrl($other);
    }
}
