<x-layouts.app title="Mejl i prijava" active="mail" eyebrow="Platforma">
    <div class="mx-auto w-full max-w-4xl space-y-5">
        @error('mail_test')
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('platform.settings.auth') }}">
            @csrf
            <x-form.section title="Registracija" desc="Ko može da napravi svoje okruženje i da li mora da potvrdi email.">
                <x-form.toggle name="registration" label="Dozvoli registraciju" desc="Isključi ako firme dodaješ samo ti." :checked="$s['registration']" />
                <x-form.toggle name="verification" label="Obavezna potvrda emaila" desc="Zahteva ispravno podešen mejl. Korisnici bez potvrde ne mogu dalje." :checked="$s['verification']" />
                <div class="sm:col-span-2"><button class="btn btn-primary">Sačuvaj</button></div>
            </x-form.section>
        </form>

        <form method="POST" action="{{ route('platform.settings.mail') }}">
            @csrf
            <x-form.section title="Slanje mejlova" desc="Koristi se za reset lozinke i potvrdu emaila.">
                <x-form.field label="Način slanja" full>
                    <select name="mailer" class="w-full field px-3.5 py-2.5 text-sm">
                        <option value="smtp" @selected(old('mailer', $s['mailer']) === 'smtp')>SMTP server</option>
                        <option value="log" @selected(old('mailer', $s['mailer']) === 'log')>Samo upiši u log (bez slanja)</option>
                    </select>
                </x-form.field>
                <x-form.field label="SMTP host"><input name="host" value="{{ old('host', $s['host']) }}" placeholder="smtp.gmail.com" class="w-full field px-3.5 py-2.5 text-sm"></x-form.field>
                <x-form.field label="Port"><input name="port" type="number" value="{{ old('port', $s['port']) }}" class="w-full field px-3.5 py-2.5 text-sm"></x-form.field>
                <x-form.field label="Enkripcija">
                    <select name="encryption" class="w-full field px-3.5 py-2.5 text-sm">
                        <option value="tls" @selected(old('encryption', $s['encryption']) === 'tls')>TLS / STARTTLS (587)</option>
                        <option value="ssl" @selected(old('encryption', $s['encryption']) === 'ssl')>SSL (465)</option>
                    </select>
                </x-form.field>
                <x-form.field label="Korisničko ime"><input name="username" value="{{ old('username', $s['username']) }}" autocomplete="off" class="w-full field px-3.5 py-2.5 text-sm"></x-form.field>
                <x-form.field label="Lozinka" :hint="$s['has_password'] ? 'Sačuvana. Ostavi prazno da je zadržiš.' : null">
                    <input name="password" type="password" autocomplete="new-password" placeholder="{{ $s['has_password'] ? '••••••••' : '' }}" class="w-full field px-3.5 py-2.5 text-sm">
                </x-form.field>
                <x-form.field label="Pošiljalac (email)"><input name="from_address" type="email" value="{{ old('from_address', $s['from_address']) }}" placeholder="no-reply@tvojdomen.rs" class="w-full field px-3.5 py-2.5 text-sm"></x-form.field>
                <x-form.field label="Pošiljalac (ime)"><input name="from_name" value="{{ old('from_name', $s['from_name']) }}" class="w-full field px-3.5 py-2.5 text-sm"></x-form.field>
                <div class="flex flex-wrap gap-2 sm:col-span-2">
                    <button class="btn btn-primary">Sačuvaj</button>
                    <button class="btn" formaction="{{ route('platform.settings.mail.test') }}" formnovalidate>Pošalji probni mejl na moj email</button>
                </div>
                <p class="text-xs text-slate-500 sm:col-span-2">Probni mejl koristi već sačuvana podešavanja; prvo sačuvaj, pa testiraj.</p>
            </x-form.section>
        </form>

        <form method="POST" action="{{ route('platform.settings.google') }}">
            @csrf
            <x-form.section title="Google prijava" desc="OAuth ključevi iz Google Cloud Console → APIs & Services → Credentials (OAuth client ID, Web application).">
                <x-form.toggle name="enabled" label="Uključi prijavu i registraciju preko Google-a" :checked="$s['google_enabled']" />
                <x-form.field label="Client ID" full><input name="client_id" value="{{ old('client_id', $s['google_client_id']) }}" class="w-full field px-3.5 py-2.5 text-sm" autocomplete="off"></x-form.field>
                <x-form.field label="Client secret" full :hint="$s['has_google_secret'] ? 'Sačuvan. Ostavi prazno da ga zadržiš.' : null">
                    <input name="client_secret" type="password" autocomplete="new-password" placeholder="{{ $s['has_google_secret'] ? '••••••••' : '' }}" class="w-full field px-3.5 py-2.5 text-sm">
                </x-form.field>
                <div class="sm:col-span-2">
                    <p class="mb-1 text-sm font-medium text-slate-700">Authorized redirect URI (nalepi u Google konzolu)</p>
                    <code class="block break-all rounded-xl bg-slate-50 px-3 py-2 text-sm">{{ $googleRedirect }}</code>
                </div>
                <div class="sm:col-span-2"><button class="btn btn-primary">Sačuvaj</button></div>
            </x-form.section>
        </form>
    </div>
</x-layouts.app>
