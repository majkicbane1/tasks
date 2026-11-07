<x-layouts.app title="Izvoz podataka" active="exports" eyebrow="Podaci">
    <div class="card p-5 sm:p-6">
        <form method="GET" action="{{ route('exports.index') }}" id="exportFilter" class="grid gap-5 sm:grid-cols-[minmax(0,22rem)_auto] sm:items-end">
            <div>
                <label for="client_id" class="mb-1.5 block text-sm font-medium">Klijent</label>
                <select id="client_id" name="client_id" class="w-full field bg-surface px-3.5 py-2.5 text-sm" onchange="this.form.submit()">
                    <option value="">Svi klijenti</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" @selected((string) $clientId === (string) $client->id)>{{ $client->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <span class="mb-1.5 block text-sm font-medium">Format</span>
                <div class="seg" role="radiogroup" aria-label="Format izvoza">
                    @foreach(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV'] as $value => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="format" value="{{ $value }}" class="peer sr-only" @checked($format === $value) onchange="this.form.submit()">
                            <span class="block rounded-full px-4 py-1.5 text-sm font-medium text-slate-500 transition peer-checked:bg-[var(--ink-btn)] peer-checked:text-[var(--bg)] peer-focus-visible:ring-2 peer-focus-visible:ring-teal-600">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </form>

        <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-slate-100 pt-5">
            <x-export-download type="all" :client-id="$clientId" :format="$format" />
            <span class="text-sm text-slate-500">
                {{ $clientId ? 'Izabrani klijent' : 'Svi klijenti' }} · sve evidencije
                {{ $format === 'xlsx' ? 'u jednom Excel fajlu (poseban list za svaku)' : 'kao ZIP sa CSV fajlovima' }}
            </span>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($types as $type => $label)
            <div class="card flex items-center justify-between gap-3 p-5">
                <h2 class="font-semibold">{{ $label }}</h2>
                <x-export-download :type="$type" :client-id="$clientId" :format="$format" />
            </div>
        @endforeach
    </div>

    <p class="px-1 text-sm text-slate-500">Fakture i uplate obuhvataju i plaćene i neplaćene račune. Taskovi sadrže ID povezane fakture/uplate. Izvoz uključuje interne napomene i skrivene stavke. Korisnički nalozi se izvoze bez lozinki i pristupnih tokena.</p>
</x-layouts.app>
