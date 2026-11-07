<x-layouts.auth title="Nadogradnja baze" subtitle="Nova verzija traži migraciju baze. Pokreni je ovde i napravi nalog platform admina (vlasnika platforme).">
    @error('migrate')
        <div class="mb-4 rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $message }}</div>
    @enderror
    <form method="POST" action="{{ route('upgrade.run') }}" onsubmit="return confirm('Pokrenuti migracije na ovoj bazi?')">
        @csrf
        <p class="mb-3 text-sm font-medium">Platform admin</p>
        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-medium">Ime</span>
            <input name="name" value="{{ old('name', 'Platform Admin') }}" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required>
        </label>
        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-medium">Email</span>
            <input name="email" type="email" value="{{ old('email') }}" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required autofocus>
            <span class="mt-1 block text-xs text-slate-500">Mora da bude nov email, različit od ovog naloga.</span>
        </label>
        <div class="mb-5 grid gap-4 sm:grid-cols-2">
            <label class="block"><span class="mb-1 block text-sm font-medium">Lozinka</span>
                <input name="password" type="password" minlength="8" autocomplete="new-password" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required></label>
            <label class="block"><span class="mb-1 block text-sm font-medium">Ponovi lozinku</span>
                <input name="password_confirmation" type="password" minlength="8" autocomplete="new-password" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required></label>
        </div>
        <button class="btn btn-primary w-full !h-12">Pokreni migracije</button>
    </form>
</x-layouts.auth>
