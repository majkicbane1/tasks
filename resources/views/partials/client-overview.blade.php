{{-- Client-facing overview: focus on what is owed. Never shows how much has been paid in total. --}}
@php
    $cur = $client->currency;
    $balance = (float) $client->balance;
    $debt = max($balance, 0);
    $pending = min((float) $client->payments()->where('type', 'payment')->where('status', 'pending_invoice')->sum('amount'), $debt);
    $notInvoiced = max($debt - $pending, 0);
    $openCount = \App\Models\WorkEntry::whereHas('project', fn ($q) => $q->where('client_id', $client->id))
        ->where('visible_to_client', true)->whereNull('paid_at')->where('amount', '>', 0)->count();
    $openText = 'Ukupan iznos koji je preostao za uplatu' . ($openCount > 0 ? ', po ' . $openCount . ($openCount === 1 ? ' otvorenom tasku' : ' otvorenih taskova') : '') . '.';

    // the chart gets counts only (items done / payments received per period), never amounts
    $workSeries = collect($series)->map(fn ($rows) => collect($rows)->map(fn ($r) => ['label' => $r['label'], 'work' => $r['count'], 'paid' => $r['paymentCount']])->all())->all();
@endphp

<div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,420px)_minmax(0,1fr)]">
    <section class="card rise relative overflow-hidden p-6 sm:p-7">
        <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full blur-3xl {{ $debt > 0 ? 'bg-rose-500/10' : 'bg-teal-500/15' }}"></div>
        <div class="relative">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold">Dugovanje</h2>
                @if($debt > 0)
                    <span class="pill pill-amber">Čeka uplatu</span>
                @else
                    <span class="pill pill-green">Izmireno</span>
                @endif
            </div>

            @if($debt > 0)
                <div class="mt-5 text-4xl font-semibold tracking-tight text-rose-700 sm:text-5xl">{{ $money($debt, $cur) }}</div>
                <p class="mt-2 text-sm text-slate-500">
                    {{ $openText }}
                </p>

                <dl class="mt-6 space-y-3 border-t border-slate-100 pt-5 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="flex items-center gap-2 text-slate-600"><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span> Fakturisano, čeka uplatu</dt>
                        <dd class="font-semibold">{{ $money($pending, $cur) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="flex items-center gap-2 text-slate-600"><span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span> Još nefakturisano</dt>
                        <dd class="font-semibold">{{ $money($notInvoiced, $cur) }}</dd>
                    </div>
                </dl>
            @else
                <div class="mt-5 flex items-center gap-4">
                    <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-teal-50 text-teal-600">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                    <div>
                        <div class="text-2xl font-semibold tracking-tight">Nema dugovanja</div>
                        <p class="text-sm text-slate-500">Svi obračunati taskovi su izmireni. Hvala!</p>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section class="card rise p-5 sm:p-6" data-chart="bars" data-series='@json($workSeries)' data-currency="{{ $cur }}" data-units='{"work":"taskova","paid":"uplata"}'>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="tabs" role="tablist">
                <button type="button" role="tab" data-metric="work" class="tab on" aria-selected="true">Poslovi</button>
                <button type="button" role="tab" data-metric="paid" class="tab" aria-selected="false">Uplate</button>
            </div>
            <div class="seg" data-range>
                <button type="button" data-value="months" class="on">Mesečno</button>
                <button type="button" data-value="years">Godišnje</button>
            </div>
        </div>
        <p class="mt-4 text-sm text-slate-500">Broj taskova, odnosno uplata, po periodima</p>
        <div data-stage class="relative mt-2 w-full overflow-hidden"></div>
    </section>
</div>
