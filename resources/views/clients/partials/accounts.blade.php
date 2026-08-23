<section class="mt-6 rounded-lg border border-slate-200 bg-white">
    <div class="border-b border-slate-200 p-4">
        <h2 class="font-semibold">Klijentski nalozi</h2>
    </div>

    <div class="border-b border-slate-200 p-4">
        <form method="POST" action="{{ route('clients.users.store', $client) }}" class="grid gap-3 lg:grid-cols-[1fr_1fr_1fr_1fr_auto] lg:items-end">
            @csrf
            <label class="block">
                <span class="mb-1 block text-xs font-medium text-slate-600">Ime</span>
                <input name="name" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required>
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-medium text-slate-600">Email</span>
                <input name="email" type="email" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required>
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-medium text-slate-600">Lozinka</span>
                <input name="password" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required minlength="8">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-medium text-slate-600">Ponovi lozinku</span>
                <input name="password_confirmation" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required minlength="8">
            </label>
            <button class="rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white">Dodaj</button>
        </form>
    </div>

    <div class="divide-y divide-slate-100">
        @forelse($client->users as $account)
            <div class="p-4">
                <form method="POST" action="{{ route('clients.users.update', [$client, $account]) }}" class="grid gap-3 lg:grid-cols-[1fr_1fr_1fr_1fr_auto] lg:items-end">
                    @csrf
                    @method('PATCH')
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Ime</span>
                        <input name="name" value="{{ old('name', $account->name) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Email</span>
                        <input name="email" type="email" value="{{ old('email', $account->email) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Nova lozinka</span>
                        <input name="password" type="password" placeholder="ostavi prazno" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" minlength="8">
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Ponovi lozinku</span>
                        <input name="password_confirmation" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" minlength="8">
                    </label>
                    <button class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Sacuvaj</button>
                </form>
                <form method="POST" action="{{ route('clients.users.destroy', [$client, $account]) }}" class="mt-2" onsubmit="return confirm('Obrisati ovaj nalog?')">
                    @csrf
                    @method('DELETE')
                    <button class="text-sm font-semibold text-rose-700">Obrisi nalog</button>
                </form>
            </div>
        @empty
            <p class="p-4 text-sm text-slate-500">Ovaj klijent nema korisnicki nalog.</p>
        @endforelse
    </div>
</section>
