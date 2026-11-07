<x-layouts.app title="Klijenti" active="clients" eyebrow="Evidencija">
    @php
        $rows = $clients->getCollection()->map(function ($c) {
            $total = $c->total_work_amount + $c->total_expenses;
            $paid = $c->total_payments;
            return (object) ['c' => $c, 'total' => $total, 'paid' => $paid, 'balance' => $total - $paid, 'pct' => $total > 0 ? min(100, $paid / $total * 100) : 0];
        });
        $totalBalance = $rows->where('balance', '>', 0)->sum('balance');
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3 text-sm text-slate-500">
            <span class="chip bg-white text-slate-700 ring-1 ring-slate-200">{{ $clients->total() }} {{ $clients->total() === 1 ? 'klijent' : 'klijenata' }}</span>
            @if(request('q'))
                <span>Pretraga: <strong class="text-slate-800">{{ request('q') }}</strong> <a href="{{ route('clients.index') }}" class="ml-1 text-teal-700 hover:underline">poništi</a></span>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-export-download type="clients" />
            <a href="{{ route('clients.create') }}" class="btn btn-primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg> Novi klijent</a>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="hidden grid-cols-[minmax(0,2fr)_90px_130px_minmax(0,1.2fr)_120px_140px_24px] items-center gap-4 border-b border-slate-100 bg-slate-50/80 px-6 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-500 lg:grid">
            <div>Klijent</div><div>Projekti</div><div>Satnica</div><div>Naplata</div><div>Status</div><div class="text-right">Preostalo</div><div></div>
        </div>
        @forelse($rows as $r)
            @php $c = $r->c; @endphp
            <a href="{{ route('clients.show', $c) }}" class="group grid gap-3 border-b border-slate-100 px-5 py-4 transition last:border-b-0 hover:bg-slate-50/80 sm:px-6 lg:grid-cols-[minmax(0,2fr)_90px_130px_minmax(0,1.2fr)_120px_140px_24px] lg:items-center lg:gap-4">
                <div class="flex min-w-0 items-center gap-3.5">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-teal-50 text-base font-semibold text-teal-600">{{ mb_strtoupper(mb_substr($c->company_name, 0, 1)) }}</span>
                    <div class="min-w-0">
                        <div class="truncate font-semibold">{{ $c->company_name }}</div>
                        <div class="truncate text-sm text-slate-500">{{ $c->contact_name ?: 'Bez kontakta' }} · {{ $c->email ?: 'bez emaila' }}</div>
                    </div>
                </div>
                <div class="text-sm"><span class="text-slate-400 lg:hidden">Projekata: </span>{{ $c->projects_count }}</div>
                <div class="text-sm text-slate-600">{{ $money($c->default_hourly_rate, $c->currency) }}/h</div>
                <div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-teal-600" style="width: {{ $r->pct }}%"></div></div>
                    <div class="mt-1 text-xs text-slate-500">{{ number_format($r->pct, 0) }}% od {{ $money($r->total, $c->currency) }}</div>
                </div>
                <div>
                    @if(! $c->is_active)
                        <span class="pill pill-gray">Neaktivan</span>
                    @elseif($r->balance > 0)
                        <span class="pill pill-amber">Duguje</span>
                    @else
                        <span class="pill pill-green">Izmireno</span>
                    @endif
                </div>
                <div class="font-semibold lg:text-right {{ $r->balance > 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ $money($r->balance, $c->currency) }}</div>
                <div class="hidden text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-teal-600 lg:block">→</div>
            </a>
        @empty
            <div class="p-10 text-center text-sm text-slate-500">{{ request('q') ? 'Nema klijenata za ovu pretragu.' : 'Još nema klijenata.' }}</div>
        @endforelse
    </div>
    <div>{{ $clients->links() }}</div>
</x-layouts.app>
