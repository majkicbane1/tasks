<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\LeaveDay;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkEntry;
use App\Notifications\LoginDetailsNotification;
use App\Services\TenantBranding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/** The platform admin manages the firms (freelancers) that each run their own environment. */
class TenantController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $tenants = Tenant::query()
            ->withCount([
                'users as admins_count' => fn ($query) => $query->where('role', User::ROLE_ADMIN),
                'clients',
                'users as client_accounts_count' => fn ($query) => $query->where('role', User::ROLE_CLIENT),
            ])
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $ids = $tenants->getCollection()->pluck('id');

        return view('platform.tenants.index', [
            'tenants' => $tenants,
            'projectCounts' => DB::table('projects')
                ->join('clients', 'clients.id', '=', 'projects.client_id')
                ->whereIn('clients.tenant_id', $ids)
                ->groupBy('clients.tenant_id')
                ->selectRaw('clients.tenant_id as tenant_id, count(*) as total')
                ->pluck('total', 'tenant_id'),
            'admins' => User::where('role', User::ROLE_ADMIN)->whereIn('tenant_id', $ids)->orderBy('name')->get()->groupBy('tenant_id'),
            'totals' => [
                'tenants' => Tenant::count(),
                'active' => Tenant::where('is_active', true)->count(),
                'clients' => Client::count(),
            ],
        ]);
    }

    public function create()
    {
        return view('platform.tenants.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        $temporary = $request->boolean('temporary');
        $send = $request->boolean('send_email');

        [$tenant, $admin] = DB::transaction(function () use ($data, $temporary) {
            $tenant = Tenant::create(['name' => $data['name']]);

            return [$tenant, $this->createAdmin($tenant, $data['admin_name'], $data['admin_email'], $data['admin_password'], $temporary)];
        });

        $warning = $send ? $this->sendLoginDetails($admin, $tenant, $data['admin_password'], $temporary) : null;

        return redirect()->route('platform.tenants.show', $tenant)
            ->with('status', 'Firma je kreirana'.($send && ! $warning ? ' i podaci za prijavu su poslati na '.$admin->email : '').'.')
            ->withErrors($warning ? ['mail' => $warning] : []);
    }

    public function show(Tenant $tenant)
    {
        return view('platform.tenants.show', [
            'tenant' => $tenant,
            'admins' => $tenant->users()->where('role', User::ROLE_ADMIN)->orderBy('name')->get(),
            'counts' => [
                'Klijenti' => Client::where('tenant_id', $tenant->id)->count(),
                'Projekti' => Project::whereIn('client_id', Client::where('tenant_id', $tenant->id)->select('id'))->count(),
                'Klijentski nalozi' => $tenant->users()->where('role', User::ROLE_CLIENT)->count(),
            ],
        ]);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $request->validate(TenantBranding::rules() + ['is_active' => ['nullable', 'boolean']]);

        TenantBranding::apply($tenant, $request, ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', 'Firma je sačuvana.');
    }

    public function storeAdmin(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $temporary = $request->boolean('temporary');
        $admin = $this->createAdmin($tenant, $data['name'], $data['email'], $data['password'], $temporary);
        $warning = $request->boolean('send_email') ? $this->sendLoginDetails($admin, $tenant, $data['password'], $temporary) : null;

        return back()->with('status', 'Admin je dodat.')->withErrors($warning ? ['mail' => $warning] : []);
    }

    public function updateAdmin(Request $request, Tenant $tenant, User $user)
    {
        abort_unless($user->tenant_id === $tenant->id && $user->isSuperAdmin(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->fill(['name' => $data['name'], 'email' => $data['email']]);
        $warning = null;
        if (! empty($data['password'])) {
            $temporary = $request->boolean('temporary');
            $user->password = Hash::make($data['password']);
            $user->must_change_password = $temporary;
            if ($request->boolean('send_email')) {
                $warning = $this->sendLoginDetails($user, $tenant, $data['password'], $temporary);
            }
        }
        $user->save();

        return back()->with('status', 'Admin je sačuvan.')->withErrors($warning ? ['mail' => $warning] : []);
    }

    public function destroyAdmin(Tenant $tenant, User $user)
    {
        abort_unless($user->tenant_id === $tenant->id && $user->isSuperAdmin(), 404);

        $user->delete();

        return back()->with('status', 'Admin je obrisan.');
    }

    /** Removes the firm together with everything in it. The firm name must be typed to confirm. */
    public function destroy(Request $request, Tenant $tenant)
    {
        $request->validate(['confirm' => ['required', 'in:'.$tenant->name]], [
            'confirm.in' => 'Ime firme se ne poklapa, brisanje nije izvršeno.',
        ]);

        DB::transaction(function () use ($tenant) {
            $clients = Client::where('tenant_id', $tenant->id)->pluck('id');
            $projects = Project::whereIn('client_id', $clients)->pluck('id');

            WorkEntry::whereIn('project_id', $projects)->delete();
            Payment::whereIn('client_id', $clients)->delete();
            Project::whereIn('id', $projects)->delete();
            Client::whereIn('id', $clients)->delete();
            LeaveDay::where('tenant_id', $tenant->id)->delete();
            User::where('tenant_id', $tenant->id)->delete();
            $tenant->delete();
        });

        \Illuminate\Support\Facades\Storage::disk(TenantBranding::DISK)->deleteDirectory("brand/{$tenant->id}");

        return redirect()->route('platform.tenants.index')->with('status', 'Firma je obrisana.');
    }

    /** Mails the login details; returns an error text instead of throwing, so the account is kept either way. */
    private function sendLoginDetails(User $user, Tenant $tenant, string $password, bool $temporary): ?string
    {
        try {
            $user->notify(new LoginDetailsNotification($tenant->name, $password, $temporary));
        } catch (\Throwable $e) {
            report($e);

            return 'Nalog je sačuvan, ali mejl nije poslat: '.$e->getMessage().' Proveri podešavanja mejla.';
        }

        return null;
    }

    private function createAdmin(Tenant $tenant, string $name, string $email, string $password, bool $temporary = false): User
    {
        return User::create([
            'tenant_id' => $tenant->id,
            'role' => User::ROLE_ADMIN,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(), // created by the platform owner, nothing to verify
            'must_change_password' => $temporary,
        ]);
    }
}
