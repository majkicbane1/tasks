<x-layouts.auth title="Dvostepena provera" subtitle="Unesi 6-cifreni kod iz Google Authenticator aplikacije ili jedan od rezervnih kodova.">
    <form method="POST" action="{{ route('two-factor.verify') }}">
        @csrf
        <label class="mb-5 block">
            <span class="mb-1 block text-sm font-medium">Kod</span>
            <input name="code" inputmode="text" autocomplete="one-time-code" maxlength="32" class="w-full field px-3 py-2.5 text-center text-xl tracking-widest outline-none focus:border-teal-600" required autofocus>
        </label>
        <button class="btn btn-primary w-full !h-12">Potvrdi</button>
    </form>
    <p class="mt-6 text-center text-sm"><a href="{{ route('login') }}" class="font-medium text-teal-700 hover:underline">Nazad na prijavu</a></p>
</x-layouts.auth>
