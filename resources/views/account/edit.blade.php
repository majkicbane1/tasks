<x-layouts.app title="Moj nalog" active="account" eyebrow="Profil">
    <div class="mx-auto w-full max-w-4xl space-y-5">
        <x-form.section title="Profil" desc="Tvoje ime se prikazuje u panelu.">
            <form method="POST" action="{{ route('account.update') }}" class="grid gap-5 sm:col-span-2 sm:grid-cols-2">
                @csrf
                @method('PATCH')
                <x-form.field label="Ime" required>
                    <input name="name" value="{{ old('name', $user->name) }}" class="w-full field px-3.5 py-2.5 text-sm" required>
                </x-form.field>
                <x-form.field label="Email" hint="Email se menja preko administratora.">
                    <input value="{{ $user->email }}" class="w-full field px-3.5 py-2.5 text-sm opacity-70" disabled>
                </x-form.field>
                <div class="sm:col-span-2"><button class="btn btn-primary">Sačuvaj profil</button></div>
            </form>
        </x-form.section>

        <x-form.section title="Lozinka" desc="Minimum 8 karaktera.">
            <form method="POST" action="{{ route('account.password') }}" class="grid gap-5 sm:col-span-2 sm:grid-cols-2">
                @csrf
                @method('PATCH')
                <x-form.field label="Trenutna lozinka" full required>
                    <input name="current_password" type="password" autocomplete="current-password" class="w-full field px-3.5 py-2.5 text-sm" required>
                </x-form.field>
                <x-form.field label="Nova lozinka" required>
                    <input name="password" type="password" minlength="8" autocomplete="new-password" class="w-full field px-3.5 py-2.5 text-sm" required>
                </x-form.field>
                <x-form.field label="Ponovi novu lozinku" required>
                    <input name="password_confirmation" type="password" minlength="8" autocomplete="new-password" class="w-full field px-3.5 py-2.5 text-sm" required>
                </x-form.field>
                <div class="sm:col-span-2"><button class="btn btn-primary">Promeni lozinku</button></div>
            </form>
            <p class="text-xs text-slate-500 sm:col-span-2">Registrovan si preko Google-a i ne znaš lozinku? Odjavi se i iskoristi „Zaboravljena lozinka“.</p>
        </x-form.section>

        <x-form.section title="Dvostepena provera (2FA)" desc="Pored lozinke traži se i kod iz Google Authenticator aplikacije.">
            <div class="space-y-5 sm:col-span-2">
                @if(session('recovery_codes'))
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-sm font-semibold text-amber-800">Sačuvaj rezervne kodove</p>
                        <p class="mt-1 text-xs text-amber-800">Svaki kod se može iskoristiti jednom ako izgubiš telefon. Ovo je jedini put da ih vidiš.</p>
                        <div class="mt-3 grid grid-cols-2 gap-2 font-mono text-sm sm:grid-cols-4">
                            @foreach(session('recovery_codes') as $code)
                                <span class="rounded-lg bg-surface px-2 py-1.5 text-center">{{ $code }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($user->hasTwoFactor())
                    <div class="flex items-center gap-3"><span class="pill pill-green">Uključeno</span><span class="text-sm text-slate-500">od {{ $user->two_factor_confirmed_at->format('d. m. Y.') }}</span></div>

                    <form method="POST" action="{{ route('account.two-factor.recovery') }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <x-form.field label="Kod iz aplikacije" hint="Potreban za nove rezervne kodove.">
                            <input name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="10" class="w-44 field px-3.5 py-2.5 text-sm" required>
                        </x-form.field>
                        <button class="btn">Nove rezervne kodove</button>
                    </form>

                    <form method="POST" action="{{ route('account.two-factor.disable') }}" class="flex flex-wrap items-end gap-3" onsubmit="return confirm('Isključiti 2FA?')">
                        @csrf
                        @method('DELETE')
                        <x-form.field label="Kod iz aplikacije" hint="Potreban za isključivanje.">
                            <input name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="10" class="w-44 field px-3.5 py-2.5 text-sm" required>
                        </x-form.field>
                        <button class="btn !text-rose-700">Isključi 2FA</button>
                    </form>
                @elseif($settingUp)
                    <ol class="list-decimal space-y-1 pl-5 text-sm text-slate-600">
                        <li>Otvori Google Authenticator i dodaj nalog skeniranjem koda (ili ručnim unosom ključa).</li>
                        <li>Unesi 6-cifreni kod koji aplikacija prikaže.</li>
                    </ol>
                    <div class="flex flex-wrap items-center gap-6">
                        <canvas data-qr="{{ $otpauth }}" width="192" height="192" class="rounded-xl bg-white p-1"></canvas>
                        <div class="min-w-0 space-y-1 text-sm">
                            <p class="text-slate-500">Ključ za ručni unos:</p>
                            <p class="break-all font-mono text-base font-semibold tracking-wider">{{ implode(' ', str_split($secret, 4)) }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('account.two-factor.confirm') }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <x-form.field label="Kod iz aplikacije">
                            <input name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="10" class="w-44 field px-3.5 py-2.5 text-sm" required autofocus>
                        </x-form.field>
                        <button class="btn btn-primary">Potvrdi i uključi</button>
                    </form>
                @else
                    <div class="flex items-center gap-3"><span class="pill pill-gray">Isključeno</span></div>
                    <form method="POST" action="{{ route('account.two-factor.start') }}">
                        @csrf
                        <button class="btn btn-primary">Uključi 2FA</button>
                    </form>
                @endif
            </div>
        </x-form.section>
    </div>
</x-layouts.app>
