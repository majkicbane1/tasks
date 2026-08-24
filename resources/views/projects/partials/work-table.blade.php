@php
    $showProject = $showProject ?? false;
    $invoiceClient = $invoiceClient ?? null;
    $canManage = ! $readonly;
    $invoiceFormId = $invoiceClient ? 'invoice-work-entries-'.$invoiceClient->id : null;
@endphp

<section class="mt-6 rounded-lg border border-slate-200 bg-white">
    <div class="border-b border-slate-200 p-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="font-semibold">Poslovi</h2>
            @if($invoiceClient)
                <form id="{{ $invoiceFormId }}" method="POST" action="{{ route('clients.work-entries.invoice', $invoiceClient) }}" class="grid gap-2 sm:grid-cols-[150px_1fr_1fr_auto]">
                    @csrf
                    <input name="paid_on" type="date" value="{{ now()->format('Y-m-d') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    <input name="reference" placeholder="Referenca racuna" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input name="note" placeholder="Napomena" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <button class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white">Fakturisi</button>
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
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            @if($invoiceClient && ! $entry->payment_id)
                                <input form="{{ $invoiceFormId }}" name="work_entry_ids[]" value="{{ $entry->id }}" type="checkbox" class="rounded border-slate-300">
                            @endif
                            <div class="font-medium">{{ $entry->title }}</div>
                        </div>
                        <div class="text-sm text-slate-500">{{ $showProject ? ($entry->project?->name ?: '-') : (optional($entry->worked_on)->format('d.m.Y.') ?: '-') }}</div>
                    </div>
                    <div class="font-semibold">{{ $money($entry->amount, $currency) }}</div>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                    <span>{{ $entry->hours ? number_format((float) $entry->hours, 2, ',', '.') . 'h' : 'Fiksno' }}</span>
                    @if($isPaid)
                        <span class="rounded-md bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">Placeno</span>
                    @elseif($isInvoiced)
                        <span class="rounded-md bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Fakturisano</span>
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
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    @if($canManage)<th class="w-10 p-3"></th>@endif
                    @if($invoiceClient)<th class="w-10 p-3"></th>@endif
                    <th class="p-3">{{ $showProject ? 'Projekat' : 'Datum' }}</th>
                    <th class="p-3">Opis</th>
                    <th class="p-3 text-right">Sati</th>
                    <th class="p-3 text-right">Ukupno</th>
                    <th class="p-3">Status</th>
                    @if($canManage)<th class="p-3"></th>@endif
                </tr>
            </thead>
            <tbody id="work-entry-sortable" data-reorder-url="{{ $canManage ? route('work-entries.reorder') : '' }}" class="divide-y divide-slate-100">
                @forelse($entries as $entry)
                    @php
                        $isPaid = (bool) $entry->paid_at;
                        $isInvoiced = (bool) $entry->invoiced_at && ! $isPaid;
                    @endphp
                    <tr data-entry-id="{{ $entry->id }}" draggable="{{ $canManage ? 'true' : 'false' }}" class="{{ $isPaid ? 'bg-emerald-50' : ($isInvoiced ? 'bg-amber-50' : '') }}">
                        @if($canManage)<td class="cursor-move p-3 text-slate-400" title="Prevuci za redosled">::</td>@endif
                        @if($invoiceClient)
                            <td class="p-3">
                                @if(! $entry->payment_id)
                                    <input form="{{ $invoiceFormId }}" name="work_entry_ids[]" value="{{ $entry->id }}" type="checkbox" class="rounded border-slate-300">
                                @endif
                            </td>
                        @endif
                        <td class="p-3">{{ $showProject ? ($entry->project?->name ?: '-') : (optional($entry->worked_on)->format('d.m.Y.') ?: '-') }}</td>
                        <td class="p-3"><div class="font-medium">{{ $entry->title }}</div><div class="text-xs text-slate-500">{{ $entry->description }}</div></td>
                        <td class="p-3 text-right">{{ $entry->hours ?: '-' }}</td>
                        <td class="p-3 text-right font-semibold">{{ $money($entry->amount, $currency) }}</td>
                        <td class="p-3">
                            @if($isPaid)
                                <span class="rounded-md bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">Placeno</span>
                            @elseif($isInvoiced)
                                <span class="rounded-md bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Fakturisano</span>
                            @else
                                <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">Otvoreno</span>
                            @endif
                        </td>
                        @if($canManage)
                            <td class="p-3"><div class="flex justify-end gap-3"><a class="font-semibold text-teal-700" href="{{ route('work-entries.edit', $entry) }}">Izmeni</a><form method="POST" action="{{ route('work-entries.destroy', $entry) }}" onsubmit="return confirm('Obrisati stavku posla?')">@csrf @method('DELETE')<button class="font-semibold text-rose-700">Obrisi</button></form></div></td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-4 text-slate-500">Nema unetih poslova.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
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
