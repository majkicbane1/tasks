<x-layouts.app :title="$client->company_name" active="clients" eyebrow="Klijent">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
        <a href="{{ route('payments.create', ['client_id' => $client->id]) }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold">+ Uplata</a>
        <a href="{{ route('projects.create', ['client_id' => $client->id]) }}" class="rounded-lg bg-teal-600 px-4 py-2.5 text-center text-sm font-semibold text-white">+ Projekat</a>
        <a href="{{ route('clients.edit', $client) }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold">Izmeni</a>
        <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Obrisati klijenta i sve njegove projekte, stavke i uplate?')">
            @csrf
            @method('DELETE')
            <button class="w-full rounded-lg border border-rose-300 px-4 py-2.5 text-center text-sm font-semibold text-rose-700 hover:bg-rose-50 sm:w-auto">Obrisi</button>
        </form>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-sm text-slate-500">Ukupno</p>
            <p class="mt-2 text-2xl font-semibold">{{ $money($client->total_work_amount + $client->total_expenses, $client->currency) }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-sm text-slate-500">Placeno</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $money($client->total_payments, $client->currency) }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-sm text-slate-500">Preostalo</p>
            <p class="mt-2 text-2xl font-semibold text-rose-700">{{ $money($client->balance, $client->currency) }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div>
            @include('projects.partials.work-table', ['entries' => $workEntries, 'readonly' => false, 'currency' => $client->currency, 'showProject' => true])
        </div>
        <div>
            @include('projects.partials.payments-table', ['payments' => $payments, 'readonly' => false, 'currency' => $client->currency])
        </div>
    </div>

</x-layouts.app>
