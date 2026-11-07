<x-layouts.app title="Dashboard" active="dashboard" eyebrow="Pregled poslovanja">
    @php
        $currency = 'EUR';
        $months = $series['months'];
        $spark = fn ($k) => array_column($months, $k);
        $palette = ['#cd5e40', '#e08b70', '#eeb4a1', '#9a9aa8', '#c9c9d2', '#f3d5ca'];
        $rows = $clients->map(function ($c) {
            $work = $c->total_work_amount; $exp = $c->total_expenses; $paid = $c->total_payments;
            $total = $work + $exp;
            return (object) ['client' => $c, 'total' => $total, 'paid' => $paid, 'balance' => $total - $paid, 'pct' => $total > 0 ? min(100, $paid / $total * 100) : 0];
        });
        $debtors = $rows->where('balance', '>', 0)->sortByDesc('balance')->values();
        $donutItems = $debtors->take(5)->map(fn ($r, $i) => ['label' => $r->client->company_name, 'value' => round($r->balance, 2), 'color' => $palette[$i]])->values();
        $rest = $debtors->slice(5)->sum('balance');
        if ($rest > 0) { $donutItems->push(['label' => 'Ostali', 'value' => round($rest, 2), 'color' => $palette[5]]); }
        $billed = $stats['work'] + $stats['expenses'];
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3 px-1">
        <p class="text-sm text-slate-500">Evo kratkog pregleda kako vam posluje biznis — {{ now()->locale('sr_Latn')->translatedFormat('l, d. F Y.') }}</p>
        <div class="flex gap-2">
            <a href="{{ route('exports.index') }}" class="btn">Izvezi izveštaj</a>
            <a href="{{ route('clients.create') }}" class="btn btn-primary">+ Klijent</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Ukupno poslova', 'value' => $money($stats['work'], $currency), 'trend' => $trends['work'], 'spark' => $spark('work'), 'color' => '#cd5e40', 'hint' => $stats['projects'].' projekata',
            'icon' => '<path d="M20 7h-9M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>'])
        @include('partials.stat-card', ['label' => 'Plaćeno', 'value' => $money($stats['paid'], $currency), 'trend' => $trends['paid'], 'spark' => $spark('paid'), 'color' => '#1f9d63', 'hint' => 'Naplaćene uplate',
            'icon' => '<rect x="2" y="5" width="20" height="14" rx="3"/><path d="M2 10h20"/>'])
        @include('partials.stat-card', ['label' => 'Troškovi', 'value' => $money($stats['expenses'], $currency), 'trend' => $trends['expenses'], 'trendGood' => false, 'spark' => $spark('expenses'), 'color' => '#e0a21b', 'hint' => 'Prefakturisani troškovi',
            'icon' => '<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>'])
        @include('partials.stat-card', ['label' => 'Preostalo', 'value' => $money($stats['balance'], $currency), 'color' => '#d6455d', 'hint' => $stats['clients'].' klijenata · neplaćeno',
            'icon' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>'])
    </div>

    @include('partials.insights', ['series' => $series, 'currency' => $currency, 'paid' => $stats['paid'], 'billed' => $billed])

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[380px_minmax(0,1fr)]">
        <section class="card rise p-5 sm:p-6">
            <h2 class="text-lg font-semibold">Dugovanja po klijentima</h2>
            <p class="text-sm text-slate-500">Raspodela preostalog iznosa</p>
            <div class="mt-4 flex justify-center" data-chart="donut" data-items='@json($donutItems)' data-label="Preostalo" data-currency="{{ $currency }}"></div>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach($donutItems as $it)
                    <li class="flex items-center justify-between gap-3">
                        <span class="flex min-w-0 items-center gap-2"><span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $it['color'] }}"></span><span class="truncate">{{ $it['label'] }}</span></span>
                        <span class="font-medium">{{ $money($it['value'], $currency) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                <div><div class="text-slate-500">Izdati računi</div><div class="font-semibold">{{ $money($invoiced, $currency) }}</div></div>
                <div class="text-right"><div class="text-slate-500">Nefakturisano</div><div class="font-semibold">{{ $money($unbilled, $currency) }}</div></div>
            </div>
        </section>

        <section class="card rise overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 p-5 sm:p-6">
                <h2 class="text-lg font-semibold">Klijenti</h2>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input data-filter="[data-client-row]" placeholder="Pretraga" class="w-44 rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-teal-300">
                    </div>
                    <a href="{{ route('clients.index') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium hover:bg-slate-50">Svi</a>
                </div>
            </div>
            <div class="hidden grid-cols-[minmax(0,1.6fr)_90px_minmax(0,1fr)_130px_110px] gap-4 border-y border-slate-100 bg-slate-50/70 px-6 py-3 text-sm text-slate-500 md:grid">
                <div>Klijent</div><div>Projekti</div><div>Naplata</div><div>Status</div><div class="text-right">Preostalo</div>
            </div>
            @forelse($rows as $r)
                <a data-client-row href="{{ route('clients.show', $r->client) }}" class="grid gap-2 border-b border-slate-100 px-5 py-4 transition last:border-b-0 hover:bg-slate-50 sm:px-6 md:grid-cols-[minmax(0,1.6fr)_90px_minmax(0,1fr)_130px_110px] md:items-center md:gap-4">
                    <div class="min-w-0">
                        <div class="truncate font-medium">{{ $r->client->company_name }}</div>
                        <div class="truncate text-sm text-slate-500">{{ $r->client->email ?: 'bez emaila' }}</div>
                    </div>
                    <div class="text-sm">{{ $r->client->projects_count }} proj.</div>
                    <div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-teal-600" style="width: {{ $r->pct }}%"></div></div>
                        <div class="mt-1 text-xs text-slate-500">{{ number_format($r->pct, 0) }}% naplaćeno</div>
                    </div>
                    <div>
                        @if($r->balance > 0)
                            <span class="chip bg-amber-50 text-amber-700">Na čekanju</span>
                        @else
                            <span class="chip chip-down">Izmireno</span>
                        @endif
                    </div>
                    <div class="font-semibold md:text-right {{ $r->balance > 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ $money($r->balance, $r->client->currency) }}</div>
                </a>
            @empty
                <p class="p-6 text-sm text-slate-500">Još nema klijenata.</p>
            @endforelse
        </section>
    </div>

    <section class="card rise">
        <div class="flex items-center justify-between p-5 sm:p-6">
            <h2 class="text-lg font-semibold">Skorašnji projekti</h2>
            <a href="{{ route('projects.index') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium hover:bg-slate-50">Svi projekti</a>
        </div>
        <div class="grid gap-3 px-5 pb-5 sm:grid-cols-2 sm:px-6 sm:pb-6 xl:grid-cols-4">
            @forelse($projects as $project)
                <a href="{{ route('projects.show', $project) }}" class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 transition hover:-translate-y-0.5 hover:border-teal-200 hover:bg-white hover:shadow-md">
                    <div class="flex items-center justify-between gap-2">
                        <span class="chip bg-white text-slate-600 ring-1 ring-slate-200">{{ ucfirst($project->status) }}</span>
                    </div>
                    <div class="mt-3 truncate font-medium">{{ $project->name }}</div>
                    <div class="truncate text-sm text-slate-500">{{ $project->client->company_name }}</div>
                    <div class="mt-3 text-lg font-semibold {{ $project->balance > 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ $money($project->balance, $project->client->currency) }}</div>
                    <div class="text-xs text-slate-500">preostalo</div>
                </a>
            @empty
                <p class="text-sm text-slate-500">Još nema projekata.</p>
            @endforelse
        </div>
    </section>
</x-layouts.app>
