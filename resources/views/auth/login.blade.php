<x-layouts.auth title="Prijava" subtitle="Evidencija poslova, uplata i dugovanja.">
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-medium">Email</span>
            <input name="email" type="email" value="{{ old('email') }}" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required autofocus>
        </label>
        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-medium">Lozinka</span>
            <input name="password" type="password" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required>
        </label>
        <div class="mb-5 flex items-center justify-between gap-2 text-sm">
            <label class="flex items-center gap-2 text-slate-600">
                <input name="remember" type="checkbox" value="1" class="rounded border-slate-300 text-teal-600">
                Zapamti me
            </label>
            <a href="{{ route('password.request') }}" class="font-medium text-teal-700 hover:underline">Zaboravljena lozinka?</a>
        </div>
        <button class="btn btn-primary w-full !h-12">Uđi u panel</button>
    </form>

    @if($googleEnabled)
        <div class="my-5 flex items-center gap-3 text-xs uppercase tracking-wide text-slate-400"><span class="h-px flex-1 bg-slate-200"></span>ili<span class="h-px flex-1 bg-slate-200"></span></div>
        <x-google-button label="Prijavi se preko Google-a" />
    @endif

    @if($registrationOpen)
        <p class="mt-6 text-center text-sm text-slate-500">Nemaš nalog? <a href="{{ route('register') }}" class="font-medium text-teal-700 hover:underline">Registruj se</a></p>
    @endif
</x-layouts.auth>
