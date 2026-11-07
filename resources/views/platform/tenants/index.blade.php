<x-layouts.app title="Firme" active="tenants" eyebrow="Platforma">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <span class="chip bg-surface text-slate-700 ring-1 ring-slate-200">{{ $totals['tenants'] }} firmi</span>
            <span class="chip bg-surface text-slate-700 ring-1 ring-slate-200">{{ $totals['active'] }} aktivnih</span>
            <span class="chip bg-surface text-slate-700 ring-1 ring-slate-200">{{ $totals['clients'] }} klijenata ukupno</span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET">
                <input name="q" value="{{ request('q') }}" placeholder="Pretraži firme…" class="field px-3.5 py-2 text-sm">
            </form>
            <a href="{{ route('platform.tenants.create') }}" class="btn btn-primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg> Nova firma</a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($tenants as $tenant)
            @php $admin = ($admins[$tenant->id] ?? collect())->first(); @endphp
            <a href="{{ route('platform.tenants.show', $tenant) }}" class="card group flex flex-col gap-5 p-5 transition hover:border-teal-300 {{ $tenant->is_active ? '' : 'opacity-70' }}">
                <div class="flex items-start justify-between gap-3">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-teal-50 text-lg font-semibold text-teal-600">{{ mb_strtoupper(mb_substr($tenant->name, 0, 1)) }}</span>
                    @if($tenant->is_active)<span class="pill pill-green">Aktivna</span>@else<span class="pill pill-gray">Deaktivirana</span>@endif
                </div>

                <div class="min-w-0">
                    <h2 class="truncate text-lg font-semibold group-hover:text-teal-700">{{ $tenant->name }}</h2>
                    <p class="mt-0.5 truncate text-sm text-slate-500">
                        @if($admin){{ $admin->name }} · {{ $admin->email }}@else Nema admina @endif
                    </p>
                </div>

                <dl class="mt-auto grid grid-cols-3 gap-2 border-t border-slate-100 pt-4 text-center">
                    <div><dd class="text-xl font-semibold">{{ $tenant->clients_count }}</dd><dt class="text-xs text-slate-500">Klijenata</dt></div>
                    <div><dd class="text-xl font-semibold">{{ $projectCounts[$tenant->id] ?? 0 }}</dd><dt class="text-xs text-slate-500">Projekata</dt></div>
                    <div><dd class="text-xl font-semibold">{{ $tenant->client_accounts_count }}</dd><dt class="text-xs text-slate-500">Naloga klijenata</dt></div>
                </dl>

                <p class="-mt-2 text-xs text-slate-400">Od {{ $tenant->created_at->format('d. m. Y.') }}
                    @if(($admins[$tenant->id] ?? collect())->count() > 1) · {{ $admins[$tenant->id]->count() }} admina @endif
                </p>
            </a>
        @empty
            <p class="card p-6 text-sm text-slate-500 sm:col-span-2 xl:col-span-3">Nema firmi{{ request('q') ? ' za ovu pretragu' : '' }}.</p>
        @endforelse
    </div>

    {{ $tenants->links() }}
</x-layouts.app>
