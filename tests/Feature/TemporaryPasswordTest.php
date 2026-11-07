<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\LoginDetailsNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TemporaryPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function platform(): User
    {
        return User::create(['role' => 'platform_admin', 'name' => 'P', 'email' => 'p@test.rs', 'password' => 'password123', 'email_verified_at' => now()]);
    }

    public function test_new_firm_gets_mail_and_must_change_the_one_time_password(): void
    {
        Notification::fake();

        $this->actingAs($this->platform())->post(route('platform.tenants.store'), [
            'name' => 'Gama', 'admin_name' => 'Gaga', 'admin_email' => 'g@test.rs', 'admin_password' => 'jednokratna1',
            'temporary' => '1', 'send_email' => '1',
        ])->assertRedirect();

        $admin = User::where('email', 'g@test.rs')->first();
        $this->assertTrue($admin->must_change_password);
        Notification::assertSentTo($admin, LoginDetailsNotification::class, function ($n) use ($admin) {
            $text = collect($n->toMail($admin)->introLines)->implode(' ');

            return str_contains($text, 'jednokratna1') && str_contains($text, 'samo za prvu prijavu');
        });

        auth()->logout();
        $this->post(route('login.store'), ['email' => 'g@test.rs', 'password' => 'jednokratna1'])->assertRedirect();

        // every page sends them to the change screen until the password is replaced
        $this->get(route('dashboard'))->assertRedirect(route('password.force'));
        $this->get(route('clients.index'))->assertRedirect(route('password.force'));
        $this->get(route('password.force'))->assertOk();

        $this->patch(route('password.force.update'), ['current_password' => 'jednokratna1', 'password' => 'jednokratna1', 'password_confirmation' => 'jednokratna1'])
            ->assertSessionHasErrors('password');

        $this->patch(route('password.force.update'), ['current_password' => 'jednokratna1', 'password' => 'mojaNova123', 'password_confirmation' => 'mojaNova123'])
            ->assertRedirect(route('dashboard'));

        $this->assertFalse($admin->fresh()->must_change_password);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_options_can_be_switched_off(): void
    {
        Notification::fake();

        $this->actingAs($this->platform())->post(route('platform.tenants.store'), [
            'name' => 'Gama', 'admin_name' => 'Gaga', 'admin_email' => 'g@test.rs', 'admin_password' => 'password123',
        ])->assertRedirect();

        $this->assertFalse(User::where('email', 'g@test.rs')->first()->must_change_password);
        Notification::assertNothingSent();
    }
}
