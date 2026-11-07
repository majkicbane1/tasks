<x-layouts.app title="Nova firma" active="tenants" eyebrow="Platforma">
    <form method="POST" action="{{ route('platform.tenants.store') }}" class="mx-auto w-full max-w-4xl space-y-5">
        @csrf
        <x-form.section title="Firma" desc="Svaka firma ima svoje okruženje: klijente, projekte, uplate i odmor.">
            <x-form.field label="Naziv firme / freelancera" full required>
                <input name="name" value="{{ old('name') }}" class="w-full field px-3.5 py-2.5 text-sm" required autofocus>
            </x-form.field>
        </x-form.section>

        <x-form.section title="Admin firme" desc="Nalog sa kojim firma upravlja svojim okruženjem. Email se smatra potvrđenim.">
            <x-form.field label="Ime" required>
                <input name="admin_name" value="{{ old('admin_name') }}" class="w-full field px-3.5 py-2.5 text-sm" required>
            </x-form.field>
            <x-form.field label="Email" required>
                <input name="admin_email" type="email" value="{{ old('admin_email') }}" class="w-full field px-3.5 py-2.5 text-sm" required>
            </x-form.field>
            <x-form.field label="Lozinka" hint="Min 8 karaktera. Admin je može promeniti u Moj nalog." full required>
                <input name="admin_password" type="password" minlength="8" autocomplete="new-password" class="w-full field px-3.5 py-2.5 text-sm" required>
            </x-form.field>
            <x-form.toggle name="temporary" label="Jednokratna lozinka" desc="Pri prvoj prijavi admin mora odmah da postavi svoju lozinku." :checked="true" />
            <x-form.toggle name="send_email" label="Pošalji podatke za prijavu mejlom" desc="Mejl sadrži email i lozinku uz napomenu da važi samo za prvu prijavu. Zahteva podešen mejl." :checked="true" />
        </x-form.section>

        <x-form.actions :back="route('platform.tenants.index')" />
    </form>
</x-layouts.app>
