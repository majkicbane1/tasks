<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UpgradeTest extends TestCase
{
    use RefreshDatabase;

    public function test_upgrade_page_is_closed_once_the_database_is_migrated(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)->get(route('upgrade.show'))->assertNotFound();
        $this->actingAs($admin)->post(route('upgrade.run'), [])->assertNotFound();
    }

    public function test_old_admin_can_run_the_migration_from_the_browser(): void
    {
        $old = User::factory()->create(['role' => 'super_admin', 'email' => 'admin@uizzard.rs']);

        // put the database back to the state before multi-tenancy
        Artisan::call('migrate:rollback', ['--step' => 3]);
        $this->assertFalse(Schema::hasTable('tenants'));
        $this->assertFalse(Schema::hasColumn('users', 'tenant_id'));

        $stale = User::find($old->id); // loaded without tenant_id, like the live session

        $this->actingAs($stale)->get(route('dashboard'))->assertRedirect(route('upgrade.show'));
        $this->actingAs($stale)->get(route('upgrade.show'))->assertOk()->assertSee('Pokreni migracije');

        $this->actingAs($stale)->post(route('upgrade.run'), [
            'name' => 'Platform', 'email' => 'owner@uizzard.rs', 'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertTrue(Schema::hasTable('tenants'));
        $this->assertDatabaseHas('users', ['email' => 'owner@uizzard.rs', 'role' => 'platform_admin']);
        // the old admin was moved into the first firm by the migration
        $this->assertNotNull(User::find($old->id)->tenant_id);
    }
}
