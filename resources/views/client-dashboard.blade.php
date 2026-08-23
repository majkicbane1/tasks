<x-layouts.app :title="$client->company_name" active="dashboard" eyebrow="Klijentski pregled">
    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Ukupno</p><p class="mt-2 text-2xl font-semibold">{{ $money($client->total_work_amount + $client->total_expenses, $client->currency) }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Placeno</p><p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $money($client->total_payments, $client->currency) }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Preostalo</p><p class="mt-2 text-2xl font-semibold text-rose-700">{{ $money($client->balance, $client->currency) }}</p></div>
    </div>
    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div>
            @include('projects.partials.work-table', ['entries' => $workEntries, 'readonly' => true, 'currency' => $client->currency, 'showProject' => true])
        </div>
        <div>
            @include('projects.partials.payments-table', ['payments' => $payments, 'readonly' => true, 'currency' => $client->currency])
        </div>
    </div>
</x-layouts.app>
