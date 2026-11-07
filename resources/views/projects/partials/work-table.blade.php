@php
    $showProject = $showProject ?? false;
    $invoiceClient = $invoiceClient ?? null;
    $canManage = ! $readonly;
    $moreUrl = $moreUrl ?? null;
    $hasMore = $hasMore ?? false;
@endphp

<section class="card overflow-hidden" @if($canManage) data-bulk data-bulk-url="{{ route('work-entries.bulk') }}" @endif>
    <div class="border-b border-slate-100 p-5">
        <div class="flex flex-col gap-3">
            <div>
                <h2 class="text-lg font-semibold">Poslovi</h2>
                @if($canManage)<p class="text-xs text-slate-500">Klikni kvadratić, pa Shift + klik za opseg.</p>@endif
            </div>
            @if($invoiceClient)
                <form data-invoice-form method="POST" action="{{ route('clients.work-entries.invoice', $invoiceClient) }}" class="flex flex-wrap items-center gap-2">
                    @csrf
                    <input name="paid_on" type="date" value="{{ now()->format('Y-m-d') }}" class="field h-10 min-w-[9rem] flex-1 px-3 text-sm" required>
                    <input name="reference" placeholder="Referenca računa" class="field h-10 min-w-[9rem] flex-1 px-3 text-sm">
                    <input name="note" placeholder="Napomena" class="field h-10 min-w-[9rem] flex-1 px-3 text-sm">
                    <button class="btn border-amber-500 bg-amber-500 text-white hover:border-amber-600 hover:bg-amber-600">Fakturiši izabrane</button>
                </form>
            @endif
        </div>
    </div>

    <div id="work-cards" data-bulk-group class="divide-y divide-slate-100 md:hidden">
        @forelse($entries as $entry)
            @include('projects.partials.work-card')
        @empty
            <p class="p-4 text-sm text-slate-500">Nema unetih poslova.</p>
        @endforelse
    </div>

    <div class="hidden overflow-x-auto md:block">
        <table class="dtable w-full text-sm">
            <thead class="text-left">
                <tr>
                    @if($canManage)
                        <th class="w-8 !pr-0"></th>
                        <th class="w-10 !pl-2 !pr-0"><input type="checkbox" data-bulk-all class="h-4 w-4 cursor-pointer rounded border-slate-300 accent-[#2fa862]" aria-label="Izaberi sve"></th>
                    @endif
                    <th>Datum</th>
                    @if($showProject)<th>Projekat</th>@endif
                    <th>Opis</th>
                    <th class="text-right">Sati</th>
                    <th class="text-right">Ukupno</th>
                    <th>Status</th>
                    @if($canManage)<th></th>@endif
                </tr>
            </thead>
            <tbody id="work-entry-sortable" data-bulk-group data-reorder-url="{{ $canManage ? route('work-entries.reorder') : '' }}" class="divide-y divide-slate-100/80">
                @forelse($entries as $entry)
                    @include('projects.partials.work-row')
                @empty
                    <tr><td colspan="9" class="p-4 text-slate-500">Nema unetih poslova.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($moreUrl && $hasMore)
        <div data-more-wrap class="border-t border-slate-100 p-4 text-center">
            <button type="button" class="btn" data-load-more data-url="{{ $moreUrl }}" data-type="work" data-page="2" data-rows="#work-entry-sortable" data-cards="#work-cards">Učitaj još</button>
        </div>
    @endif

    @if(method_exists($entries, 'links'))
        <div class="border-t border-slate-100 p-4">
            {{ $entries->links() }}
        </div>
    @endif

    @if($canManage)
        <div data-bulk-bar class="fixed bottom-4 left-1/2 z-50 hidden w-[min(64rem,calc(100vw_-_1.5rem))] flex-wrap items-center gap-x-3 gap-y-2 rounded-2xl border border-slate-200 bg-surface/95 p-3 pl-4 shadow-[0_20px_50px_-15px_rgb(20_20_26/.35)] backdrop-blur lg:left-[calc(50%_+_9.5rem)] lg:w-[min(64rem,calc(100vw_-_22rem))]">
            <span class="whitespace-nowrap text-sm font-semibold"><span data-bulk-count class="inline-grid h-6 min-w-6 place-items-center rounded-full bg-teal-600 px-2 text-xs text-white">0</span> <span class="ml-1">izabrano</span></span>
            <span class="hidden h-6 w-px bg-slate-200 lg:block"></span>

            <div class="flex min-w-0 flex-1 basis-56 items-center gap-2">
                <input type="date" data-bulk-date value="{{ now()->format('Y-m-d') }}" aria-label="Novi datum" class="field h-10 min-w-0 flex-1 px-3 text-sm">
                <button type="button" data-bulk-apply class="btn">Datum</button>
            </div>

            <div class="flex min-w-0 flex-1 basis-56 items-center gap-2" title="Menja samo oznaku na stavci. Ne pravi račune i ne menja dugovanje.">
                <select data-bulk-state aria-label="Novi status" class="field h-10 min-w-0 flex-1 px-3 text-sm">
                    <option value="open">Otvoreno</option>
                    <option value="invoiced">Fakturisano</option>
                    <option value="paid">Plaćeno</option>
                </select>
                <button type="button" data-bulk-apply-state class="btn">Status</button>
            </div>

            <button type="button" data-bulk-delete data-delete-url="{{ route('work-entries.bulk-destroy') }}" class="btn btn-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg> Obriši
            </button>
            <button type="button" data-bulk-clear class="icon-sm" title="Poništi izbor (Esc)"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
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
