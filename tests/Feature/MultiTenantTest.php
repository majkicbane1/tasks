<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Totp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;

class MultiTenantTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $firm): User
    {
        $tenant = Tenant::create(['name' => $firm]);

        return User::create([
            'tenant_id' => $tenant->id, 'role' => 'super_admin', 'name' => $firm.' admin',
            'email' => strtolower($firm).'@test.rs', 'password' => 'password123', 'email_verified_at' => now(),
        ]);
    }

    private function client(User $admin, string $name): Client
    {
        return Client::create(['tenant_id' => $admin->tenant_id, 'company_name' => $name, 'default_hourly_rate' => 10, 'currency' => 'EUR']);
    }

    public function test_firms_cannot_see_or_touch_each_others_clients(): void
    {
        $a = $this->admin('Alfa');
        $b = $this->admin('Beta');
        $alfaClient = $this->client($a, 'Alfa klijent');
        $betaClient = $this->client($b, 'Beta klijent');

        $this->actingAs($a)->get(route('clients.index'))
            ->assertOk()->assertSee('Alfa klijent')->assertDontSee('Beta klijent');

        $this->actingAs($a)->get(route('clients.show', $betaClient))->assertNotFound();
        $this->actingAs($a)->delete(route('clients.destroy', $betaClient))->assertNotFound();

        // a project for someone else's client is rejected by validation, not silently created
        $this->actingAs($a)->post(route('projects.store'), [
            'client_id' => $betaClient->id, 'name' => 'Hack', 'status' => 'active',
        ])->assertSessionHasErrors('client_id');

        $this->actingAs($a)->post(route('projects.store'), [
            'client_id' => $alfaClient->id, 'name' => 'OK', 'status' => 'active',
        ])->assertSessionHasNoErrors();
    }

    public function test_new_clients_and_leave_days_belong_to_the_creating_firm(): void
    {
        $a = $this->admin('Alfa');
        $b = $this->admin('Beta');

        $this->actingAs($a)->post(route('leave.store'), ['date' => '2026-01-05', 'portion' => '1.00']);
        $this->actingAs($b)->post(route('leave.store'), ['date' => '2026-01-05', 'portion' => '0.50']);

        $this->assertDatabaseCount('leave_days', 2); // same day allowed in different firms
    }

    public function test_only_platform_admin_reaches_platform_pages(): void
    {
        $a = $this->admin('Alfa');
        $this->actingAs($a)->get(route('platform.tenants.index'))->assertForbidden();

        $platform = User::create(['role' => 'platform_admin', 'name' => 'P', 'email' => 'p@test.rs', 'password' => 'password123', 'email_verified_at' => now()]);
        $this->actingAs($platform)->get(route('platform.tenants.index'))->assertOk()->assertSee('Alfa');
        $this->actingAs($platform)->get(route('platform.settings'))->assertOk();
        $this->actingAs($platform)->get(route('clients.index'))->assertRedirect(route('platform.tenants.index'));
        $this->actingAs($platform)->get(route('dashboard'))->assertRedirect(route('platform.tenants.index'));

        $this->actingAs($platform)->post(route('platform.tenants.store'), [
            'name' => 'Gama', 'admin_name' => 'G', 'admin_email' => 'g@test.rs', 'admin_password' => 'password123',
        ])->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'g@test.rs', 'role' => 'super_admin']);
    }

    public function test_registration_creates_firm_and_can_be_closed(): void
    {
        $this->post(route('register.store'), [
            'tenant_name' => 'Nova', 'name' => 'Pera', 'email' => 'pera@test.rs',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'pera@test.rs')->first();
        $this->assertSame('super_admin', $user->role);
        $this->assertSame('Nova', $user->tenant->name);

        auth()->logout();
        Setting::put('auth.registration_enabled', '0');
        $this->get(route('register'))->assertNotFound();
    }

    public function test_verification_email_is_sent_when_required(): void
    {
        Notification::fake();
        Setting::put('auth.require_verification', '1');

        $this->post(route('register.store'), [
            'tenant_name' => 'Nova', 'name' => 'Pera', 'email' => 'pera@test.rs',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect(route('verification.notice'));

        Notification::assertSentTo(User::where('email', 'pera@test.rs')->first(), VerifyEmail::class);
        $this->get(route('dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_password_reset_mail_and_deactivated_firm(): void
    {
        Notification::fake();
        $a = $this->admin('Alfa');

        $this->post(route('password.email'), ['email' => $a->email])->assertSessionHas('status');
        Notification::assertSentTo($a, ResetPassword::class);

        $a->tenant->update(['is_active' => false]);
        $this->post(route('login.store'), ['email' => $a->email, 'password' => 'password123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_two_factor_login_flow(): void
    {
        $a = $this->admin('Alfa');

        $this->actingAs($a)->post(route('account.two-factor.start'));
        $secret = $a->fresh()->two_factor_secret;
        $this->post(route('account.two-factor.confirm'), ['code' => Totp::code($secret)])
            ->assertSessionHas('recovery_codes');
        $this->assertTrue($a->fresh()->hasTwoFactor());

        auth()->logout();
        $this->post(route('login.store'), ['email' => $a->email, 'password' => 'password123'])
            ->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();

        $this->post(route('two-factor.verify'), ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();

        // the code used during setup cannot be replayed, a recovery code works once
        $recovery = $a->fresh()->two_factor_recovery_codes[0];
        $this->post(route('two-factor.verify'), ['code' => $recovery])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($a);
        $this->assertNotContains($recovery, $a->fresh()->two_factor_recovery_codes);
    }
}
