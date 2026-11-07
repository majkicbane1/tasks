<x-layouts.app :title="$client->company_name" active="dashboard" eyebrow="Klijentski pregled">
    @php
        $cur = $client->currency;
        $series = $client->financeSeries(true);
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
            @include('projects.partials.work-table', ['entries' => $workEntries, 'readonly' => true, 'currency' => $client->currency, 'showProject' => true])
        </div>
        <div>
            @include('projects.partials.payments-table', ['payments' => $payments, 'readonly' => true, 'currency' => $client->currency])
        </div>
    </div>
</x-layouts.app>
