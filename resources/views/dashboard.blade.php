<x-layouts.app title="Dashboard" active="dashboard">
    @php($currency = 'EUR')
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Ukupno poslova</p><p class="mt-2 text-2xl font-semibold">{{ $money($stats['work'], $currency) }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Plaćeno</p><p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $money($stats['paid'], $currency) }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Troškovi</p><p class="mt-2 text-2xl font-semibold text-amber-700">{{ $money($stats['expenses'], $currency) }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Preostalo</p><p class="mt-2 text-2xl font-semibold text-rose-700">{{ $money($stats['balance'], $currency) }}</p></div>
    </div>
    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_380px]">
        <section class="rounded-lg border border-slate-200 bg-white">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 p-4">
                <h2 class="font-semibold">Klijenti</h2>
                <a href="{{ route('clients.create') }}" class="rounded-lg bg-teal-600 px-3 py-2 text-sm font-semibold text-white">+ Klijent</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($clients as $client)
                    <a href="{{ route('clients.show', $client) }}" class="grid gap-2 p-4 hover:bg-slate-50 sm:grid-cols-[1fr_auto]">
                        <div>
                            <div class="font-medium">{{ $client->company_name }}</div>
                            <div class="text-sm text-slate-500">{{ $client->projects_count }} projekata · {{ $client->email ?: 'bez emaila' }}</div>
                        </div>
                        <div class="text-left sm:text-right">
                            <div class="font-semibold {{ $client->balance > 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ $money($client->balance, $client->currency) }}</div>
                            <div class="text-xs text-slate-500">preostalo</div>
                        </div>
                    </a>
                @empty
                    <p class="p-4 text-sm text-slate-500">Još nema klijenata.</p>
                @endforelse
            </div>
        </section>
        <section class="rounded-lg border border-slate-200 bg-white">
            <div class="border-b border-slate-200 p-4"><h2 class="font-semibold">Skorašnji projekti</h2></div>
            <div class="divide-y divide-slate-100">
                @forelse($projects as $project)
                    <a href="{{ route('projects.show', $project) }}" class="block p-4 hover:bg-slate-50">
                        <div class="font-medium">{{ $project->name }}</div>
                        <div class="text-sm text-slate-500">{{ $project->client->company_name }} · {{ $money($project->balance, $project->client->currency) }} ostalo</div>
                    </a>
                @empty
                    <p class="p-4 text-sm text-slate-500">Još nema projekata.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
