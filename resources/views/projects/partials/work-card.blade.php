@php
    $isPaid = (bool) $entry->paid_at;
    $isInvoiced = (bool) $entry->invoiced_at && ! $isPaid;
    $date = $entry->worked_on?->format('d.m.Y.') ?: '—';
@endphp
<div class="p-4 {{ ($mutePaid ?? false) && $isPaid ? 'opacity-50' : '' }}" data-entry-id="{{ $entry->id }}">
    <div class="grid grid-cols-[minmax(0,1fr)_max-content] items-start gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-2.5">
                @if($canManage)
                    <input type="checkbox" value="{{ $entry->id }}" data-bulk-item data-invoiceable="{{ $entry->payment_id ? 0 : 1 }}" class="h-4 w-4 shrink-0 rounded border-slate-300 accent-[#2fa862]" aria-label="Izaberi task">
                @endif
                <div class="min-w-0 break-words font-semibold">{{ $entry->title }}</div>
            </div>
            <div class="mt-0.5 break-words text-sm text-slate-500"><span data-date-cell="{{ $entry->id }}">{{ $date }}</span>@if($showProject) · {{ $entry->project?->name ?: '-' }}@endif</div>
        </div>
        <div class="whitespace-nowrap text-right font-semibold">{{ $money($entry->amount, $currency) }}</div>
    </div>
    <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-600">
        <span>{{ $entry->hours ? number_format((float) $entry->hours, 2, ',', '.') . 'h' : 'Fiksno' }}</span>
        <span data-status-cell="{{ $entry->id }}">
            @if($isPaid)
                <span class="pill pill-green">Plaćeno</span>
            @elseif($isInvoiced)
                <span class="pill pill-amber">Fakturisano</span>
            @endif
        </span>
    </div>
    @if($canManage)
        <div class="mt-3 flex gap-1">
            <a class="icon-sm" title="Izmeni" href="{{ route('work-entries.edit', $entry) }}"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
            <form method="POST" action="{{ route('work-entries.destroy', $entry) }}" onsubmit="return confirm('Obrisati task?')">
                @csrf
                @method('DELETE')
                <button class="icon-sm danger" title="Obriši"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg></button>
            </form>
        </div>
    @endif
</div>
