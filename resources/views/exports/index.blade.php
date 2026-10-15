<x-layouts.app title="Izvoz podataka" active="exports">
    <div class="mb-6 rounded-lg border border-slate-200 bg-white p-5">
        <p class="text-sm text-slate-600">Izaberite Excel (.xlsx) ili CSV i preuzmite sve poslovne podatke ili pojedinačne evidencije. Excel izvoz svih podataka sadrži poseban list za svaku evidenciju; CSV izvoz svih podataka preuzima se kao ZIP arhiva.</p>
        <form method="GET" action="{{ route('exports.index') }}" class="mt-4 flex flex-wrap items-end gap-3">
            <div>
                <label for="client_id" class="mb-1 block text-sm font-medium">Klijent</label>
                <select id="client_id" name="client_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 sm:w-80">
                    <option value="">Svi klijenti</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" @selected((string) $clientId === (string) $client->id)>{{ $client->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Primeni filter</button>
        </form>
        <div class="mt-5 flex flex-wrap items-center gap-3">
            <x-export-download type="all" :client-id="$clientId" :format="$format" />
            <span class="text-sm text-slate-500">{{ $clientId ? 'Izvoz za izabranog klijenta' : 'Izvoz za sve klijente' }} · sve stavke, bez ograničenja na trenutnu stranicu</span>
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($types as $type => $label)
            <div class="space-y-4 rounded-lg border border-slate-200 bg-white p-5">
                <h2 class="font-semibold">{{ $label }}</h2>
                <x-export-download :type="$type" :client-id="$clientId" :format="$format" />
            </div>
        @endforeach
    </div>
    <p class="mt-4 text-sm text-slate-500">Fakture i uplate obuhvataju i plaćene i neplaćene račune. Taskovi sadrže ID povezane fakture/uplate. Izvoz uključuje interne napomene i skrivene stavke. Korisnički nalozi se izvoze bez lozinki i pristupnih tokena.</p>
</x-layouts.app>
