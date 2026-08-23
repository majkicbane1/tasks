<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkEntry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'admin@uizzard.rs',
        ], [
            'name' => 'Super Admin',
            'role' => 'super_admin',
            'password' => bcrypt('password123'),
        ]);

        $client = Client::updateOrCreate([
            'company_name' => 'Demo Klijent',
        ], [
            'contact_name' => 'Demo Kontakt',
            'email' => 'klijent@example.com',
            'default_hourly_rate' => 20,
            'currency' => 'EUR',
            'is_active' => true,
        ]);

        User::updateOrCreate([
            'email' => 'klijent@example.com',
        ], [
            'client_id' => $client->id,
            'name' => 'Demo Klijent',
            'role' => 'client',
            'password' => bcrypt('password123'),
        ]);

        $project = Project::firstOrCreate([
            'client_id' => $client->id,
            'name' => 'Website održavanje',
        ], [
            'status' => 'active',
            'hourly_rate' => 20,
            'description' => 'Demo projekat za proveru obračuna.',
        ]);

        WorkEntry::firstOrCreate([
            'project_id' => $project->id,
            'title' => 'Selected Guides - new design',
        ], [
            'worked_on' => now()->subDays(5),
            'hours' => 24,
            'hourly_rate' => 20,
            'amount' => 480,
            'status' => 'billable',
            'visible_to_client' => true,
        ]);

        Payment::firstOrCreate([
            'client_id' => $client->id,
            'paid_on' => now()->subDays(2)->toDateString(),
            'amount' => 200,
        ], [
            'project_id' => $project->id,
            'type' => 'payment',
            'method' => 'Bank transfer',
            'note' => 'Demo uplata',
            'visible_to_client' => true,
        ]);
    }
}
