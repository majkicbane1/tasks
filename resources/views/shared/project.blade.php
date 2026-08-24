<x-layouts.shared :title="$project->name" :eyebrow="$client->company_name">
    <x-slot:nav>
        <nav class="flex gap-2">
            <a rel="nofollow" href="{{ route('shared.dashboard', $shareToken) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold">Pregled</a>
            <a rel="nofollow" href="{{ route('shared.projects', $shareToken) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold">Projekti</a>
        </nav>
    </x-slot:nav>

    @php($currency = $client->currency)
    <div class="grid gap-3 sm:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Poslovi</p><p class="mt-2 text-2xl font-semibold">{{ $money($project->billable_total, $currency) }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Troskovi</p><p class="mt-2 text-2xl font-semibold text-amber-700">{{ $money($project->expenses_total, $currency) }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Placeno</p><p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $money($project->payments_total, $currency) }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Preostalo</p><p class="mt-2 text-2xl font-semibold text-rose-700">{{ $money($project->balance, $currency) }}</p></div>
    </div>

    @include('projects.partials.work-table', ['entries' => $project->workEntries, 'readonly' => true, 'currency' => $currency])
    @include('projects.partials.payments-table', ['payments' => $project->payments, 'readonly' => true, 'currency' => $currency, 'allowMarkPaid' => false])
</x-layouts.shared>
