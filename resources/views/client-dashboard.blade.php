<x-layouts.app :title="$client->company_name" active="dashboard" eyebrow="Klijentski pregled">
    @include('partials.client-overview', ['client' => $client, 'series' => $client->financeSeries(true)])

    <div class="grid grid-cols-1 items-start gap-5 2xl:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
        <div>
            @include('projects.partials.work-table', ['entries' => $workEntries, 'readonly' => true, 'currency' => $client->currency, 'showProject' => true, 'mutePaid' => true])
        </div>
        <div>
            @include('projects.partials.payments-table', ['payments' => $payments, 'readonly' => true, 'currency' => $client->currency])
        </div>
    </div>
</x-layouts.app>
