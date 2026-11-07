<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $tenant = Tenant::create(['name' => 'Alfa']);

        return User::create(['tenant_id' => $tenant->id, 'role' => 'super_admin', 'name' => 'A', 'email' => 'a@test.rs', 'password' => 'password123', 'email_verified_at' => now()]);
    }

    public function test_admin_uploads_logos_and_favicon_and_they_are_shown(): void
    {
        Storage::fake('local');
        $admin = $this->admin();

        $this->actingAs($admin)->patch(route('firm.update'), [
            'name' => 'Alfa d.o.o.',
            'logo_light' => UploadedFile::fake()->image('light.png', 200, 60),
            'logo_dark' => UploadedFile::fake()->image('dark.png', 200, 60),
            'favicon' => UploadedFile::fake()->image('fav.png', 64, 64),
        ])->assertSessionHasNoErrors();

        $tenant = $admin->tenant->fresh();
        $this->assertSame('Alfa d.o.o.', $tenant->name);
        foreach (['logo_light', 'logo_dark', 'favicon'] as $slot) {
            Storage::disk('local')->assertExists($tenant->{$slot});
            $this->get($tenant->brandUrl($slot))->assertOk();
        }

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertSee($tenant->brandUrl('logo_light'), false)
            ->assertSee($tenant->brandUrl('logo_dark'), false)
            ->assertSee('rel="icon"', false);
    }

    public function test_replacing_and_removing_deletes_the_old_file(): void
    {
        Storage::fake('local');
        $admin = $this->admin();

        $this->actingAs($admin)->patch(route('firm.update'), ['name' => 'Alfa', 'logo_light' => UploadedFile::fake()->image('a.png')]);
        $first = $admin->tenant->fresh()->logo_light;

        $this->patch(route('firm.update'), ['name' => 'Alfa', 'logo_light' => UploadedFile::fake()->image('b.png')]);
        Storage::disk('local')->assertMissing($first);

        $second = $admin->tenant->fresh()->logo_light;
        $this->patch(route('firm.update'), ['name' => 'Alfa', 'remove' => ['logo_light']]);
        Storage::disk('local')->assertMissing($second);
        $this->assertNull($admin->tenant->fresh()->logo_light);
    }

    public function test_non_images_are_rejected(): void
    {
        Storage::fake('local');

        $this->actingAs($this->admin())->patch(route('firm.update'), [
            'name' => 'Alfa', 'logo_light' => UploadedFile::fake()->create('evil.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('logo_light');
    }

    public function test_platform_admin_can_set_branding_for_a_firm(): void
    {
        Storage::fake('local');
        $tenant = $this->admin()->tenant;
        $platform = User::create(['role' => 'platform_admin', 'name' => 'P', 'email' => 'p@test.rs', 'password' => 'password123', 'email_verified_at' => now()]);

        $this->actingAs($platform)->patch(route('platform.tenants.update', $tenant), [
            'name' => 'Alfa', 'is_active' => '1', 'favicon' => UploadedFile::fake()->image('f.png', 32, 32),
        ])->assertSessionHasNoErrors();

        $this->assertNotNull($tenant->fresh()->favicon);
    }
}
