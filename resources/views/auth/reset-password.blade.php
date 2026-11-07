<x-layouts.auth title="Nova lozinka">
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-medium">Email</span>
            <input name="email" type="email" value="{{ old('email', $email) }}" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required>
        </label>
        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-medium">Nova lozinka</span>
            <input name="password" type="password" minlength="8" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required autofocus>
        </label>
        <label class="mb-5 block">
            <span class="mb-1 block text-sm font-medium">Ponovi lozinku</span>
            <input name="password_confirmation" type="password" minlength="8" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required>
        </label>
        <button class="btn btn-primary w-full !h-12">Sačuvaj lozinku</button>
    </form>
</x-layouts.auth>
