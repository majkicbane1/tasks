@php
    $isPaid = (bool) $entry->paid_at;
    $isInvoiced = (bool) $entry->invoiced_at && ! $isPaid;
    $date = $entry->worked_on?->format('d.m.Y.') ?: '—';
@endphp
<tr data-entry-id="{{ $entry->id }}" @if($canManage) draggable="true" @endif class="group {{ ($mutePaid ?? false) && $isPaid ? 'opacity-50' : '' }}">
    @if($canManage)
        <td class="w-8 cursor-grab !pr-0 text-slate-300 group-hover:text-slate-500" title="Prevuci za redosled"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg></td>
        <td class="w-10 !pl-2 !pr-0"><input type="checkbox" value="{{ $entry->id }}" data-bulk-item data-invoiceable="{{ $entry->payment_id ? 0 : 1 }}" class="h-4 w-4 cursor-pointer rounded border-slate-300 accent-[#2fa862]" aria-label="Izaberi stavku"></td>
    @endif
    <td class="whitespace-nowrap text-slate-600" data-date-cell="{{ $entry->id }}">{{ $date }}</td>
    @if($showProject)<td class="text-slate-600">{{ $entry->project?->name ?: '-' }}</td>@endif
    <td><div class="font-semibold">{{ $entry->title }}</div><div class="text-xs text-slate-500">{{ $entry->description }}</div></td>
    <td class="text-right">{{ $entry->hours ?: '-' }}</td>
    <td class="whitespace-nowrap text-right font-semibold">{{ $money($entry->amount, $currency) }}</td>
    <td data-status-cell="{{ $entry->id }}">
        @if($isPaid)
            <span class="pill pill-green">Plaćeno</span>
        @elseif($isInvoiced)
            <span class="pill pill-amber">Fakturisano</span>
        @else
            <span class="pill pill-gray">Otvoreno</span>
        @endif
    </td>
    @if($canManage)
        <td><div class="flex justify-end gap-1"><a class="icon-sm" title="Izmeni" href="{{ route('work-entries.edit', $entry) }}"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a><form method="POST" action="{{ route('work-entries.destroy', $entry) }}" onsubmit="return confirm('Obrisati stavku posla?')">@csrf @method('DELETE')<button class="icon-sm danger" title="Obriši"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg></button></form></div></td>
    @endif
</tr>
