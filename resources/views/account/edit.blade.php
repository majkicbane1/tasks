<x-layouts.app title="Moj nalog" active="account" eyebrow="Profil">
    @php
        $initials = collect(explode(' ', trim($user->name)))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
        $roleLabel = match (true) { $user->isPlatformAdmin() => 'Platform admin', $user->isSuperAdmin() => 'Admin firme', default => 'Klijent' };
        $input = 'w-full field px-3.5 py-2.5 text-sm';
    @endphp

    {{-- identity --}}
    <div class="card flex flex-wrap items-center gap-5 p-5 sm:p-6">
        <span class="grid h-16 w-16 shrink-0 place-items-center rounded-full bg-gradient-to-br from-teal-300 to-teal-600 text-xl font-semibold text-white">{{ $initials }}</span>
        <div class="min-w-0 flex-1">
            <h2 class="truncate text-xl font-semibold">{{ $user->name }}</h2>
            <p class="truncate text-sm text-slate-500">{{ $user->email }}@if($user->tenant) · {{ $user->tenant->name }}@endif</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="pill pill-gray">{{ $roleLabel }}</span>
            @if($user->hasVerifiedEmail())<span class="pill pill-green">Email potvrđen</span>@else<span class="pill pill-amber">Email nije potvrđen</span>@endif
            @if($user->hasTwoFactor())<span class="pill pill-green">2FA uključen</span>@else<span class="pill pill-gray">2FA isključen</span>@endif
            @if($user->google_id)<span class="pill pill-gray">Google</span>@endif
        </div>
    </div>

    <div class="grid items-start gap-5 xl:grid-cols-2">
        {{-- left: profile + password --}}
        <div class="space-y-5">
            <form method="POST" action="{{ route('account.update') }}" class="card space-y-5 p-5 sm:p-6">
                @csrf
                @method('PATCH')
                <div>
                    <h2 class="text-base font-semibold">Profil</h2>
                    <p class="mt-1 text-sm text-slate-500">Ime se prikazuje u panelu.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.field label="Ime" required><input name="name" value="{{ old('name', $user->name) }}" class="{{ $input }}" required></x-form.field>
                    <x-form.field label="Email" hint="Menja se preko administratora."><input value="{{ $user->email }}" class="{{ $input }} opacity-70" disabled></x-form.field>
                </div>
                <div class="flex justify-end"><button class="btn btn-primary">Sačuvaj profil</button></div>
            </form>

            <form method="POST" action="{{ route('account.password') }}" class="card space-y-5 p-5 sm:p-6">
                @csrf
                @method('PATCH')
                <div>
                    <h2 class="text-base font-semibold">Lozinka</h2>
                    <p class="mt-1 text-sm text-slate-500">Minimum 8 karaktera. Ako si se registrovao preko Google-a i ne znaš lozinku, odjavi se i iskoristi „Zaboravljena lozinka“.</p>
                </div>
                <x-form.field label="Trenutna lozinka" required><input name="current_password" type="password" autocomplete="current-password" class="{{ $input }}" required></x-form.field>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.field label="Nova lozinka" required><input name="password" type="password" minlength="8" autocomplete="new-password" class="{{ $input }}" required></x-form.field>
                    <x-form.field label="Ponovi novu lozinku" required><input name="password_confirmation" type="password" minlength="8" autocomplete="new-password" class="{{ $input }}" required></x-form.field>
                </div>
                <div class="flex justify-end"><button class="btn btn-primary">Promeni lozinku</button></div>
            </form>
        </div>

        {{-- right: 2FA --}}
        <section class="card space-y-5 p-5 sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold">Dvostepena provera (2FA)</h2>
                    <p class="mt-1 text-sm text-slate-500">Pored lozinke traži se i kod iz Google Authenticator aplikacije.</p>
                </div>
                @if($user->hasTwoFactor())<span class="pill pill-green">Uključeno</span>@else<span class="pill pill-gray">Isključeno</span>@endif
            </div>

            @if(session('recovery_codes'))
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm font-semibold text-amber-800">Sačuvaj rezervne kodove</p>
                    <p class="mt-1 text-xs text-amber-800">Svaki kod važi jednom, ako izgubiš telefon. Ovo je jedini put da ih vidiš.</p>
                    <div class="mt-3 grid grid-cols-2 gap-2 font-mono text-sm">
                        @foreach(session('recovery_codes') as $code)
                            <span class="rounded-lg bg-surface px-2 py-1.5 text-center">{{ $code }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($user->hasTwoFactor())
                <p class="text-sm text-slate-500">Uključeno od {{ $user->two_factor_confirmed_at->format('d. m. Y.') }} · preostalo rezervnih kodova: {{ count($user->two_factor_recovery_codes ?? []) }}</p>

                <div class="grid gap-4 border-t border-slate-100 pt-5 sm:grid-cols-2">
                    <form method="POST" action="{{ route('account.two-factor.recovery') }}" class="space-y-3 rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                        @csrf
                        <div class="text-sm font-medium">Novi rezervni kodovi</div>
                        <x-form.field label="Kod iz aplikacije"><input name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="10" class="{{ $input }}" required></x-form.field>
                        <button class="btn w-full">Napravi nove kodove</button>
                    </form>
                    <form method="POST" action="{{ route('account.two-factor.disable') }}" class="space-y-3 rounded-2xl border border-rose-200 p-4" onsubmit="return confirm('Isključiti 2FA?')">
                        @csrf
                        @method('DELETE')
                        <div class="text-sm font-medium text-rose-700">Isključi 2FA</div>
                        <x-form.field label="Kod iz aplikacije"><input name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="10" class="{{ $input }}" required></x-form.field>
                        <button class="btn w-full !text-rose-700">Isključi</button>
                    </form>
                </div>
            @elseif($settingUp)
                <ol class="list-decimal space-y-1 pl-5 text-sm text-slate-600">
                    <li>Otvori Google Authenticator i dodaj nalog skeniranjem koda (ili ručnim unosom ključa).</li>
                    <li>Unesi 6-cifreni kod koji aplikacija prikaže.</li>
                </ol>
                <div class="flex flex-wrap items-center gap-6 rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                    <canvas data-qr="{{ $otpauth }}" width="192" height="192" class="rounded-xl bg-white p-1"></canvas>
                    <div class="min-w-0 space-y-1 text-sm">
                        <p class="text-slate-500">Ključ za ručni unos:</p>
                        <p class="break-all font-mono text-base font-semibold tracking-wider">{{ implode(' ', str_split($secret, 4)) }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('account.two-factor.confirm') }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <x-form.field label="Kod iz aplikacije"><input name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="10" class="w-44 field px-3.5 py-2.5 text-sm" required autofocus></x-form.field>
                    <button class="btn btn-primary">Potvrdi i uključi</button>
                </form>
            @else
                <ul class="space-y-2 text-sm text-slate-600">
                    <li class="flex gap-2"><span class="text-teal-600">✓</span> Zaštita i ako neko sazna lozinku</li>
                    <li class="flex gap-2"><span class="text-teal-600">✓</span> Radi sa Google Authenticator, Authy i 1Password</li>
                    <li class="flex gap-2"><span class="text-teal-600">✓</span> Dobijaš rezervne kodove za slučaj gubitka telefona</li>
                </ul>
                <form method="POST" action="{{ route('account.two-factor.start') }}">
                    @csrf
                    <button class="btn btn-primary">Uključi 2FA</button>
                </form>
            @endif
        </section>
    </div>
</x-layouts.app>
