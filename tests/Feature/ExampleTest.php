<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_super_admin_can_open_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($admin)->get('/');

        $response->assertOk();
        $response->assertSee('Dashboard');
    }

    public function test_super_admin_can_open_project_create_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($admin)->get('/projects/create');

        $response->assertOk();
        $response->assertSee('Novi projekat');
    }

    public function test_super_admin_can_create_project_with_client_hourly_rate(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);
        $client = Client::create([
            'company_name' => 'Test Firma',
            'default_hourly_rate' => 35,
            'currency' => 'EUR',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post('/projects', [
            'client_id' => $client->id,
            'name' => 'Novi projekat',
            'status' => 'active',
            'hourly_rate' => $client->default_hourly_rate,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'client_id' => $client->id,
            'name' => 'Novi projekat',
            'hourly_rate' => 35,
        ]);
    }

    public function test_super_admin_can_reset_client_user_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);
        $client = Client::create([
            'company_name' => 'Test Firma',
            'default_hourly_rate' => 35,
            'currency' => 'EUR',
            'is_active' => true,
        ]);
        $clientUser = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'client',
            'password' => bcrypt('old-password'),
        ]);

        $response = $this->actingAs($admin)->patch("/clients/{$client->id}/users/{$clientUser->id}/password", [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('clients.show', $client));
        $this->assertTrue(auth()->attempt([
            'email' => $clientUser->email,
            'password' => 'new-password',
        ]));
    }

    public function test_pending_invoice_does_not_count_until_marked_paid(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);
        $client = Client::create([
            'company_name' => 'Test Firma',
            'default_hourly_rate' => 35,
            'currency' => 'EUR',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post('/payments', [
            'client_id' => $client->id,
            'paid_on' => now()->toDateString(),
            'amount' => 120,
            'type' => 'payment',
            'invoice_issued' => '1',
            'visible_to_client' => '1',
        ])->assertRedirect();

        $payment = Payment::first();
        $this->assertSame('pending_invoice', $payment->status);
        $this->assertSame(0.0, $client->fresh()->total_payments);

        $this->actingAs($admin)->patch("/payments/{$payment->id}/paid")->assertRedirect();

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame(120.0, $client->fresh()->total_payments);
    }

    public function test_super_admin_can_delete_client_project_and_work_entry(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);
        $client = Client::create([
            'company_name' => 'Test Firma',
            'default_hourly_rate' => 35,
            'currency' => 'EUR',
            'is_active' => true,
        ]);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Test projekat',
            'status' => 'active',
        ]);
        $entry = WorkEntry::create([
            'project_id' => $project->id,
            'title' => 'Test stavka',
            'hours' => 1,
            'hourly_rate' => 35,
            'amount' => 35,
        ]);

        $this->actingAs($admin)->delete("/work-entries/{$entry->id}")->assertRedirect();
        $this->assertDatabaseMissing('work_entries', ['id' => $entry->id]);

        $this->actingAs($admin)->delete("/projects/{$project->id}")->assertRedirect();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);

        $this->actingAs($admin)->delete("/clients/{$client->id}")->assertRedirect();
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_super_admin_can_create_and_update_client_user_from_client_edit(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);
        $client = Client::create([
            'company_name' => 'Test Firma',
            'default_hourly_rate' => 35,
            'currency' => 'EUR',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post("/clients/{$client->id}/users", [
            'name' => 'Client User',
            'email' => 'client-user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('clients.edit', $client));

        $clientUser = User::where('email', 'client-user@example.com')->firstOrFail();

        $this->actingAs($admin)->patch("/clients/{$client->id}/users/{$clientUser->id}", [
            'name' => 'Updated Client User',
            'email' => 'updated-client-user@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect(route('clients.edit', $client));

        $this->assertDatabaseHas('users', [
            'id' => $clientUser->id,
            'client_id' => $client->id,
            'role' => 'client',
            'name' => 'Updated Client User',
            'email' => 'updated-client-user@example.com',
        ]);
        $this->assertTrue(auth()->attempt([
            'email' => 'updated-client-user@example.com',
            'password' => 'newpassword123',
        ]));
    }
}
