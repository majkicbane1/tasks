<section class="mt-6 rounded-lg border border-slate-200 bg-white">
    @php
        $allowMarkPaid = $allowMarkPaid ?? true;
    @endphp
    <div class="border-b border-slate-200 p-4"><h2 class="font-semibold">Uplate i racuni</h2></div>
    <div class="divide-y divide-slate-100">
        @forelse($payments as $payment)
            @php
                $isPending = $payment->isPendingInvoice();
                $isExpense = $payment->type === 'expense';
            @endphp
            <div class="grid gap-2 p-4 md:grid-cols-[110px_1fr_140px_170px] md:items-center {{ $isPending ? 'bg-amber-50' : '' }}">
                <div class="text-sm text-slate-500">{{ optional($payment->paid_on)->format('d.m.Y.') }}</div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="break-all font-medium">{{ $payment->reference ?: ($isExpense ? 'Trosak' : 'Bez reference') }}</span>
                        @if($isPending)
                            <span class="inline-flex rounded-md bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Izdato</span>
                        @elseif($isExpense)
                            <span class="inline-flex rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">Trosak</span>
                        @else
                            <span class="inline-flex rounded-md bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">Placeno</span>
                        @endif
                    </div>
                    <div class="text-sm text-slate-500">{{ $payment->project ? $payment->project->name.' - ' : '' }}{{ $payment->note ?: $payment->method }}</div>
                </div>
                <div class="font-semibold md:text-right {{ $isPending ? 'text-amber-800' : ($isExpense ? 'text-amber-700' : 'text-emerald-700') }}">
                    {{ $isExpense ? '+' : ($isPending ? '' : '-') }}{{ $money($payment->amount, $currency) }}
                </div>
                <div class="flex gap-3 md:justify-end">
                    @if($isPending && $allowMarkPaid)
                        <form method="POST" action="{{ route('payments.mark-paid', $payment) }}">
                            @csrf
                            @method('PATCH')
                            <button class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white">Placeno</button>
                        </form>
                    @endif
                    @unless($readonly)
                        <a class="self-center text-sm font-semibold text-teal-700" href="{{ route('payments.edit', $payment) }}">Izmeni</a>
                        <form method="POST" action="{{ route('payments.destroy', $payment) }}" onsubmit="return confirm('Obrisati uplatu/racun?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm font-semibold text-rose-700">Obrisi</button>
                        </form>
                    @endunless
                </div>
            </div>
        @empty
            <p class="p-4 text-sm text-slate-500">Nema uplata.</p>
        @endforelse
    </div>
</section>
