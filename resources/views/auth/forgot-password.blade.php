<x-layouts.auth title="Zaboravljena lozinka" subtitle="Unesi email i poslaćemo ti link za novu lozinku.">
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <label class="mb-5 block">
            <span class="mb-1 block text-sm font-medium">Email</span>
            <input name="email" type="email" value="{{ old('email') }}" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required autofocus>
        </label>
        <button class="btn btn-primary w-full !h-12">Pošalji link</button>
    </form>
    <p class="mt-6 text-center text-sm"><a href="{{ route('login') }}" class="font-medium text-teal-700 hover:underline">Nazad na prijavu</a></p>
</x-layouts.auth>
