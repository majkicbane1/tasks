<x-layouts.app :title="$client->company_name" active="clients" eyebrow="Klijent">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
        <a href="{{ route('exports.index', ['client_id' => $client->id]) }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold">Izvoz podataka</a>
        <form method="POST" action="{{ route('clients.share', $client) }}">
            @csrf
            <button class="w-full rounded-lg border border-teal-300 px-4 py-2.5 text-center text-sm font-semibold text-teal-700 hover:bg-teal-50 sm:w-auto">Share / Podeli</button>
        </form>
        <a href="{{ route('payments.create', ['client_id' => $client->id]) }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold">+ Uplata</a>
        <a href="{{ route('projects.create', ['client_id' => $client->id]) }}" class="rounded-lg bg-teal-600 px-4 py-2.5 text-center text-sm font-semibold text-white">+ Projekat</a>
        <a href="{{ route('clients.edit', $client) }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold">Izmeni</a>
        <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Obrisati klijenta i sve njegove projekte, stavke i uplate?')">
            @csrf
            @method('DELETE')
            <button class="w-full rounded-lg border border-rose-300 px-4 py-2.5 text-center text-sm font-semibold text-rose-700 hover:bg-rose-50 sm:w-auto">Obrisi</button>
        </form>
    </div>

    @if($client->share_token)
        <div class="mb-4 rounded-lg border border-teal-200 bg-teal-50 p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="text-sm font-semibold text-teal-900">Share link</div>
                    <a rel="nofollow" href="{{ route('shared.dashboard', $client->share_token) }}" target="_blank" class="break-all text-sm text-teal-800 underline">{{ route('shared.dashboard', $client->share_token) }}</a>
                </div>
                <form method="POST" action="{{ route('clients.share', $client) }}">
                    @csrf
                    <input type="hidden" name="regenerate" value="1">
                    <button class="rounded-lg border border-teal-300 px-3 py-2 text-sm font-semibold text-teal-800 hover:bg-teal-100">Novi link</button>
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

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div>
            @include('projects.partials.work-table', ['entries' => $workEntries, 'readonly' => false, 'currency' => $client->currency, 'showProject' => true, 'invoiceClient' => $client])
        </div>
        <div>
            @include('projects.partials.payments-table', ['payments' => $payments, 'readonly' => false, 'currency' => $client->currency])
        </div>
    </div>

</x-layouts.app>
