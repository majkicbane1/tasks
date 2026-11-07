<x-layouts.app :title="$client->company_name" active="clients" eyebrow="Klijent">
    <div class="flex flex-wrap items-center justify-end gap-2">
        <a href="{{ route('exports.index', ['client_id' => $client->id]) }}" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg> Izvoz</a>
        <form method="POST" action="{{ route('clients.share', $client) }}">
            @csrf
            <button class="btn btn-soft"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/></svg> Podeli</button>
        </form>
        <a href="{{ route('payments.create', ['client_id' => $client->id]) }}" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg> Uplata</a>
        <a href="{{ route('projects.create', ['client_id' => $client->id]) }}" class="btn btn-primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg> Projekat</a>
        <span class="mx-1 hidden h-6 w-px bg-slate-200 sm:block"></span>
        <a href="{{ route('clients.edit', $client) }}" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg> Izmeni</a>
        <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Obrisati klijenta i sve njegove projekte, stavke i uplate?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg> Obriši</button>
        </form>
    </div>

    @if($client->share_token)
        @php $shareUrl = route('shared.dashboard', $client->share_token); @endphp
        <div class="card flex flex-col gap-3 p-4 sm:p-5 lg:flex-row lg:items-center">
            <div class="flex min-w-0 flex-1 items-center gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-teal-50 text-teal-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/></svg></span>
                <div class="min-w-0">
                    <div class="text-sm font-semibold">Share link</div>
                    <a rel="nofollow" href="{{ $shareUrl }}" target="_blank" class="block truncate text-sm text-slate-500 hover:text-teal-700">{{ $shareUrl }}</a>
                </div>
            </div>
            <div class="flex shrink-0 gap-2">
                <button type="button" class="btn btn-soft" data-copy="{{ $shareUrl }}" data-label="Kopiraj link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> <span>Kopiraj link</span></button>
                <a rel="nofollow" href="{{ $shareUrl }}" target="_blank" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg> Otvori</a>
                <form method="POST" action="{{ route('clients.share', $client) }}">
                    @csrf
                    <input type="hidden" name="regenerate" value="1">
                    <button class="btn" onclick="return confirm('Stari link će prestati da radi. Napraviti novi?')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 15-6.7L21 8M21 3v5h-5M21 12a9 9 0 0 1-15 6.7L3 16M3 21v-5h5"/></svg> Novi link</button>
                </form>
            </div>
        </div>
    @endif

    @php
        $cur = $client->currency;
        $series = $client->financeSeries(false);
        $billed = $client->total_work_amount + $client->total_expenses;
        $spark = fn ($k) => array_column($series['months'], $k);
    @endphp
    <div class="grid gap-5 sm:grid-cols-3">
        @include('partials.stat-card', ['label' => 'Ukupno', 'value' => $money($billed, $cur), 'spark' => $spark('work'), 'color' => '#cd5e40', 'hint' => 'Poslovi i troškovi', 'icon' => '<path d="M20 7h-9M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>'])
        @include('partials.stat-card', ['label' => 'Plaćeno', 'value' => $money($client->total_payments, $cur), 'spark' => $spark('paid'), 'color' => '#1f9d63', 'hint' => 'Naplaćene uplate', 'icon' => '<rect x="2" y="5" width="20" height="14" rx="3"/><path d="M2 10h20"/>'])
        @include('partials.stat-card', ['label' => 'Preostalo', 'value' => $money($client->balance, $cur), 'color' => '#d6455d', 'hint' => 'Za uplatu', 'icon' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>'])
    </div>
    @include('partials.insights', ['series' => $series, 'currency' => $cur, 'paid' => $client->total_payments, 'billed' => $billed])

    <div class="grid grid-cols-1 items-start gap-5 2xl:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
        <div>
            @include('projects.partials.work-table', ['entries' => $workEntries, 'readonly' => false, 'currency' => $client->currency, 'showProject' => true, 'invoiceClient' => $client])
        </div>
        <div>
            @include('projects.partials.payments-table', ['payments' => $payments, 'readonly' => false, 'currency' => $client->currency])
        </div>
    </div>

</x-layouts.app>
