@php
    $total = max((float) ($billed ?? 0), 0);
    $paidAmount = (float) ($paid ?? 0);
    $pct = $total > 0 ? min(100, $paidAmount / $total * 100) : 0;
@endphp
<div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">
    <section class="card rise p-5 sm:p-6" data-chart="bars" data-series='@json($series)' data-currency="{{ $currency }}">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <h2 class="text-lg font-semibold">Pregled prihoda</h2>
            <div class="seg" data-range>
                <button type="button" data-value="months" class="on">Mesečno</button>
                <button type="button" data-value="years">Godišnje</button>
            </div>
        </div>
        <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
            <div class="text-3xl font-semibold tracking-tight sm:text-4xl" data-total>—</div>
            <div class="flex flex-wrap gap-4 text-sm">
                <button type="button" data-metric="work" class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-teal-600"></span> Poslovi</button>
                <button type="button" data-metric="paid" class="flex items-center gap-2 opacity-40"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Plaćeno</button>
                <button type="button" data-metric="expenses" class="flex items-center gap-2 opacity-40"><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span> Troškovi</button>
            </div>
        </div>
        <div data-stage class="relative mt-2 w-full overflow-hidden"></div>
    </section>

    <section class="card rise p-5 sm:p-6">
        <h2 class="text-lg font-semibold">Naplata</h2>
        <div data-chart="gauge" data-value="{{ $pct }}" data-label="Naplaćeno" class="mt-2"></div>
        <div class="mt-3 flex items-end justify-between text-sm">
            <div><div class="text-slate-500">Plaćeno</div><div class="text-lg font-semibold">{{ $money($paidAmount, $currency) }}</div></div>
            <div class="text-right"><div class="text-slate-500">Ukupno</div><div class="text-lg font-semibold">{{ $money($total, $currency) }}</div></div>
        </div>
        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-teal-600" style="width: {{ $pct }}%"></div></div>
    </section>
</div>
