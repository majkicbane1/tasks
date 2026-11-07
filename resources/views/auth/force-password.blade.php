<x-layouts.auth title="Postavi svoju lozinku" subtitle="Prijavio si se jednokratnom lozinkom. Pre nego što nastaviš, izaberi svoju.">
    <form method="POST" action="{{ route('password.force.update') }}">
        @csrf
        @method('PATCH')
        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-medium">Jednokratna lozinka</span>
            <input name="current_password" type="password" autocomplete="current-password" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required autofocus>
        </label>
        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-medium">Nova lozinka</span>
            <input name="password" type="password" minlength="8" autocomplete="new-password" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required>
        </label>
        <label class="mb-5 block">
            <span class="mb-1 block text-sm font-medium">Ponovi novu lozinku</span>
            <input name="password_confirmation" type="password" minlength="8" autocomplete="new-password" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required>
        </label>
        <button class="btn btn-primary w-full !h-12">Sačuvaj i nastavi</button>
    </form>
    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button class="btn w-full">Odjavi se</button>
    </form>
</x-layouts.auth>
