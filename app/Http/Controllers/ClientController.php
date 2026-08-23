<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::withCount('projects')->latest()->paginate(15);

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create', ['client' => new Client()]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedClient($request);
        $userData = $request->validate([
            'user_name' => ['nullable', 'string', 'max:255'],
            'user_email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'user_password' => ['nullable', 'string', 'min:8'],
        ]);

        $client = Client::create($data);

        if (! empty($userData['user_email'])) {
            User::create([
                'client_id' => $client->id,
                'role' => 'client',
                'name' => $userData['user_name'] ?: $client->contact_name ?: $client->company_name,
                'email' => $userData['user_email'],
                'password' => Hash::make($userData['user_password'] ?: 'password123'),
            ]);
        }

        return redirect()->route('clients.show', $client)->with('status', 'Klijent je kreiran.');
    }

    public function show(Client $client)
    {
        $client->load(['users']);
        $workEntries = WorkEntry::whereHas('project', fn ($query) => $query->where('client_id', $client->id))
            ->with('project')
            ->latest('worked_on')
            ->get();
        $payments = $client->payments()->with('project')->latest('paid_on')->get();

        return view('clients.show', compact('client', 'workEntries', 'payments'));
    }

    public function edit(Client $client)
    {
        $client->load('users');

        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $client->update($this->validatedClient($request));

        return redirect()->route('clients.show', $client)->with('status', 'Klijent je sačuvan.');
    }

    public function destroy(Client $client)
    {
        $client->users()->delete();
        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Klijent je obrisan.');
    }

    public function resetUserPassword(Request $request, Client $client, User $user)
    {
        abort_unless((int) $user->client_id === (int) $client->id && $user->role === 'client', 404);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('clients.show', $client)->with('status', 'Lozinka za '.$user->email.' je resetovana.');
    }

    public function storeUser(Request $request, Client $client)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'client_id' => $client->id,
            'role' => 'client',
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('clients.edit', $client)->with('status', 'Nalog je kreiran.');
    }

    public function updateUser(Request $request, Client $client, User $user)
    {
        abort_unless((int) $user->client_id === (int) $client->id && $user->role === 'client', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('clients.edit', $client)->with('status', 'Nalog je sacuvan.');
    }

    public function destroyUser(Client $client, User $user)
    {
        abort_unless((int) $user->client_id === (int) $client->id && $user->role === 'client', 404);

        $user->delete();

        return redirect()->route('clients.edit', $client)->with('status', 'Nalog je obrisan.');
    }

    private function validatedClient(Request $request): array
    {
        return $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'default_hourly_rate' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
        ]) + ['is_active' => false];
    }
}
