<x-layouts.app :title="$project->name" active="projects" :eyebrow="$project->client->company_name">
    @php($currency = $project->client->currency)
    @if(auth()->user()->isSuperAdmin())
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('work-entries.create', ['project_id' => $project->id]) }}" class="rounded-lg bg-teal-600 px-4 py-2.5 text-center text-sm font-semibold text-white">+ Stavka posla</a>
            <a href="{{ route('payments.create', ['client_id' => $project->client_id]) }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold">+ Uplata</a>
            <a href="{{ route('projects.edit', $project) }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold">Izmeni projekat</a>
            <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Obrisati projekat i sve njegove stavke?')">
                @csrf
                @method('DELETE')
                <button class="w-full rounded-lg border border-rose-300 px-4 py-2.5 text-center text-sm font-semibold text-rose-700 hover:bg-rose-50 sm:w-auto">Obrisi projekat</button>
            </form>
        </div>
    @endif
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Poslovi', 'value' => $money($project->billable_total, $currency), 'color' => '#cd5e40', 'hint' => 'Ukupno obračunato', 'icon' => '<path d="M20 7h-9M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>'])
        @include('partials.stat-card', ['label' => 'Troškovi', 'value' => $money($project->expenses_total, $currency), 'color' => '#e0a21b', 'hint' => 'Prefakturisano', 'icon' => '<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>'])
        @include('partials.stat-card', ['label' => 'Plaćeno', 'value' => $money($project->payments_total, $currency), 'color' => '#1f9d63', 'hint' => 'Naplaćene uplate', 'icon' => '<rect x="2" y="5" width="20" height="14" rx="3"/><path d="M2 10h20"/>'])
        @include('partials.stat-card', ['label' => 'Preostalo', 'value' => $money($project->balance, $currency), 'color' => '#d6455d', 'hint' => 'Za uplatu', 'icon' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>'])
    </div>
    @include('projects.partials.work-table', ['entries' => $project->workEntries, 'readonly' => ! auth()->user()->isSuperAdmin(), 'currency' => $currency])
    @include('projects.partials.payments-table', ['payments' => $project->payments, 'readonly' => ! auth()->user()->isSuperAdmin(), 'currency' => $currency])
</x-layouts.app>
