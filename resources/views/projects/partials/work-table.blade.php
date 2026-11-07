@php
    $showProject = $showProject ?? false;
    $invoiceClient = $invoiceClient ?? null;
    $canManage = ! $readonly;
    $invoiceFormId = $invoiceClient ? 'invoice-work-entries-'.$invoiceClient->id : null;
@endphp

<section class="card overflow-hidden">
    <div class="border-b border-slate-100 p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-lg font-semibold">Poslovi</h2>
            @if($invoiceClient)
                <form id="{{ $invoiceFormId }}" method="POST" action="{{ route('clients.work-entries.invoice', $invoiceClient) }}" class="flex flex-wrap items-center gap-2">
                    @csrf
                    <input name="paid_on" type="date" value="{{ now()->format('Y-m-d') }}" class="min-w-[9rem] flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-teal-300 focus:bg-white" required>
                    <input name="reference" placeholder="Referenca racuna" class="min-w-[9rem] flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-teal-300 focus:bg-white">
                    <input name="note" placeholder="Napomena" class="min-w-[9rem] flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-teal-300 focus:bg-white">
                    <button class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">Fakturisi</button>
                </form>
            @endif
        </div>
    </div>

    <div class="divide-y divide-slate-100 md:hidden">
        @forelse($entries as $entry)
            @php
                $isPaid = (bool) $entry->paid_at;
                $isInvoiced = (bool) $entry->invoiced_at && ! $isPaid;
            @endphp
            <div class="p-4 {{ $isPaid ? 'bg-emerald-50' : ($isInvoiced ? 'bg-amber-50' : '') }}">
                <div class="grid grid-cols-[minmax(0,1fr)_max-content] items-start gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            @if($invoiceClient && ! $entry->payment_id)
                                <input form="{{ $invoiceFormId }}" name="work_entry_ids[]" value="{{ $entry->id }}" type="checkbox" class="rounded border-slate-300">
                            @endif
                            <div class="min-w-0 break-words font-medium">{{ $entry->title }}</div>
                        </div>
                        <div class="break-words text-sm text-slate-500">{{ $showProject ? ($entry->project?->name ?: '-') : (optional($entry->worked_on)->format('d.m.Y.') ?: '-') }}</div>
                    </div>
                    <div class="whitespace-nowrap text-right font-semibold">{{ $money($entry->amount, $currency) }}</div>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                    <span>{{ $entry->hours ? number_format((float) $entry->hours, 2, ',', '.') . 'h' : 'Fiksno' }}</span>
                    @if($isPaid)
                        <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">Placeno</span>
                    @elseif($isInvoiced)
                        <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Fakturisano</span>
                    @endif
                </div>
                @if($canManage)
                    <div class="mt-3 flex gap-3">
                        <a class="text-sm font-semibold text-teal-700" href="{{ route('work-entries.edit', $entry) }}">Izmeni</a>
                        <form method="POST" action="{{ route('work-entries.destroy', $entry) }}" onsubmit="return confirm('Obrisati stavku posla?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm font-semibold text-rose-700">Obrisi</button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <p class="p-4 text-sm text-slate-500">Nema unetih poslova.</p>
        @endforelse
    </div>

    <div class="hidden overflow-x-auto md:block">
        <table class="dtable w-full text-sm">
            <thead class="text-left">
                <tr>
                    @if($canManage)<th class="w-10 px-4 py-3.5"></th>@endif
                    @if($invoiceClient)<th class="w-10 px-4 py-3.5"></th>@endif
                    <th class="px-4 py-3.5">{{ $showProject ? 'Projekat' : 'Datum' }}</th>
                    <th class="px-4 py-3.5">Opis</th>
                    <th class="px-4 py-3.5 text-right">Sati</th>
                    <th class="px-4 py-3.5 text-right">Ukupno</th>
                    <th class="px-4 py-3.5">Status</th>
                    @if($canManage)<th class="px-4 py-3.5"></th>@endif
                </tr>
            </thead>
            <tbody id="work-entry-sortable" data-reorder-url="{{ $canManage ? route('work-entries.reorder') : '' }}" class="divide-y divide-slate-100/80">
                @forelse($entries as $entry)
                    @php
                        $isPaid = (bool) $entry->paid_at;
                        $isInvoiced = (bool) $entry->invoiced_at && ! $isPaid;
                    @endphp
                    <tr data-entry-id="{{ $entry->id }}" draggable="{{ $canManage ? 'true' : 'false' }}" class="group">
                        @if($canManage)<td class="w-8 cursor-grab !pr-0 text-slate-300 group-hover:text-slate-500" title="Prevuci za redosled"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg></td>@endif
                        @if($invoiceClient)
                            <td class="px-4 py-3.5">
                                @if(! $entry->payment_id)
                                    <input form="{{ $invoiceFormId }}" name="work_entry_ids[]" value="{{ $entry->id }}" type="checkbox" class="rounded border-slate-300">
                                @endif
                            </td>
                        @endif
                        <td class="px-4 py-3.5">{{ $showProject ? ($entry->project?->name ?: '-') : (optional($entry->worked_on)->format('d.m.Y.') ?: '-') }}</td>
                        <td class="px-4 py-3.5"><div class="font-semibold">{{ $entry->title }}</div><div class="text-xs text-slate-500">{{ $entry->description }}</div></td>
                        <td class="px-4 py-3.5 text-right">{{ $entry->hours ?: '-' }}</td>
                        <td class="px-4 py-3.5 text-right font-semibold">{{ $money($entry->amount, $currency) }}</td>
                        <td class="px-4 py-3.5">
                            @if($isPaid)
                                <span class="pill pill-green">Plaćeno</span>
                            @elseif($isInvoiced)
                                <span class="pill pill-amber">Fakturisano</span>
                            @else
                                <span class="pill pill-gray">Otvoreno</span>
                            @endif
                        </td>
                        @if($canManage)
                            <td class="px-4 py-3.5"><div class="flex justify-end gap-1"><a class="icon-sm" title="Izmeni" href="{{ route('work-entries.edit', $entry) }}"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a><form method="POST" action="{{ route('work-entries.destroy', $entry) }}" onsubmit="return confirm('Obrisati stavku posla?')">@csrf @method('DELETE')<button class="icon-sm danger" title="Obriši"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg></button></form></div></td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-4 text-slate-500">Nema unetih poslova.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($entries, 'links'))
        <div class="border-t border-slate-200 p-4">
            {{ $entries->links() }}
        </div>
    @endif
</section>

@if($canManage)
    <script>
        (() => {
            const tbody = document.getElementById('work-entry-sortable');
            if (!tbody || !tbody.dataset.reorderUrl) return;

            let dragged = null;

            tbody.addEventListener('dragstart', (event) => {
                const row = event.target.closest('tr[data-entry-id]');
                if (!row) return;
                dragged = row;
                row.classList.add('opacity-50');
            });

            tbody.addEventListener('dragend', () => {
                dragged?.classList.remove('opacity-50');
                dragged = null;
            });

            tbody.addEventListener('dragover', (event) => {
                event.preventDefault();
                const row = event.target.closest('tr[data-entry-id]');
                if (!row || row === dragged) return;
                const rect = row.getBoundingClientRect();
                const after = event.clientY > rect.top + rect.height / 2;
                tbody.insertBefore(dragged, after ? row.nextSibling : row);
            });

            tbody.addEventListener('drop', async () => {
                const entries = Array.from(tbody.querySelectorAll('tr[data-entry-id]')).map((row) => row.dataset.entryId);
                await fetch(tbody.dataset.reorderUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ entries }),
                });
            });
        })();
    </script>
@endif
