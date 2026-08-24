<x-layouts.shared :title="$client->company_name" eyebrow="Podeljeni pregled">
    <x-slot:nav>
        <nav class="flex gap-2">
            <a rel="nofollow" href="{{ route('shared.dashboard', $shareToken) }}" class="rounded-lg bg-teal-600 px-3 py-2 text-sm font-semibold text-white">Pregled</a>
            <a rel="nofollow" href="{{ route('shared.projects', $shareToken) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold">Projekti</a>
        </nav>
    </x-slot:nav>

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
            @include('projects.partials.payments-table', ['payments' => $payments, 'readonly' => true, 'currency' => $client->currency, 'allowMarkPaid' => false])
        </div>
    </div>
</x-layouts.shared>
