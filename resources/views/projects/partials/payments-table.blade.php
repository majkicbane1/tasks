<section class="card overflow-hidden">
    @php
        $allowMarkPaid = $allowMarkPaid ?? true;
    @endphp
    <div class="border-b border-slate-100 p-5"><h2 class="text-lg font-semibold">Uplate i racuni</h2></div>
    <div class="divide-y divide-slate-100">
        @forelse($payments as $payment)
            @php
                $isPending = $payment->isPendingInvoice();
                $isExpense = $payment->type === 'expense';
            @endphp
            <div class="grid gap-2 px-5 py-4 md:grid-cols-[96px_minmax(0,1fr)_auto_auto] md:items-center transition hover:bg-slate-50">
                <div class="text-sm text-slate-500">{{ optional($payment->paid_on)->format('d.m.Y.') }}</div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="break-words font-semibold">{{ $payment->reference ?: ($isExpense ? 'Trosak' : 'Bez reference') }}</span>
                        @if($isPending)
                            <span class="pill pill-amber">Izdato</span>
                        @elseif($isExpense)
                            <span class="pill pill-gray">Trošak</span>
                        @else
                            <span class="pill pill-green">Plaćeno</span>
                        @endif
                    </div>
                    <div class="text-sm text-slate-500">{{ $payment->project ? $payment->project->name.' - ' : '' }}{{ $payment->note ?: $payment->method }}</div>
                </div>
                <div class="whitespace-nowrap font-semibold md:text-right {{ $isPending ? 'text-amber-800' : ($isExpense ? 'text-amber-700' : 'text-emerald-700') }}">
                    {{ $isExpense ? '+' : ($isPending ? '' : '-') }}{{ $money($payment->amount, $currency) }}
                </div>
                <div class="flex items-center gap-1 md:justify-end">
                    @if($isPending && $allowMarkPaid)
                        <form method="POST" action="{{ route('payments.mark-paid', $payment) }}">
                            @csrf
                            @method('PATCH')
                            <button class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white">Placeno</button>
                        </form>
                    @endif
                    @unless($readonly)
                        <a class="icon-sm" title="Izmeni" href="{{ route('payments.edit', $payment) }}"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
                        <form method="POST" action="{{ route('payments.destroy', $payment) }}" onsubmit="return confirm('Obrisati uplatu/racun?')">
                            @csrf
                            @method('DELETE')
                            <button class="icon-sm danger" title="Obriši"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg></button>
                        </form>
                    @endunless
                </div>
            </div>
        @empty
            <p class="p-4 text-sm text-slate-500">Nema uplata.</p>
        @endforelse
    </div>
</section>
