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
    <div class="grid gap-3 sm:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Poslovi</p><p class="mt-2 text-2xl font-semibold">{{ $money($project->billable_total, $currency) }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Troškovi</p><p class="mt-2 text-2xl font-semibold text-amber-700">{{ $money($project->expenses_total, $currency) }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Plaćeno</p><p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $money($project->payments_total, $currency) }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Preostalo</p><p class="mt-2 text-2xl font-semibold text-rose-700">{{ $money($project->balance, $currency) }}</p></div>
    </div>
    @include('projects.partials.work-table', ['entries' => $project->workEntries, 'readonly' => ! auth()->user()->isSuperAdmin(), 'currency' => $currency])
    @include('projects.partials.payments-table', ['payments' => $project->payments, 'readonly' => ! auth()->user()->isSuperAdmin(), 'currency' => $currency])
</x-layouts.app>
