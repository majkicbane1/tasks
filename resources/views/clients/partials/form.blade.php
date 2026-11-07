<x-form.section title="Podaci o firmi" desc="Osnovni kontakt i pravni podaci klijenta.">
    <x-form.field label="Naziv firme" :full="true" :required="true"><input name="company_name" value="{{ old('company_name', $client->company_name) }}" class="field h-11 w-full px-3.5" required></x-form.field>
    <x-form.field label="Kontakt osoba"><input name="contact_name" value="{{ old('contact_name', $client->contact_name) }}" class="field h-11 w-full px-3.5"></x-form.field>
    <x-form.field label="Email firme"><input name="email" type="email" value="{{ old('email', $client->email) }}" class="field h-11 w-full px-3.5"></x-form.field>
    <x-form.field label="Telefon"><input name="phone" value="{{ old('phone', $client->phone) }}" class="field h-11 w-full px-3.5"></x-form.field>
    <x-form.field label="Website"><input name="website" value="{{ old('website', $client->website) }}" class="field h-11 w-full px-3.5"></x-form.field>
    <x-form.field label="PIB / ID"><input name="tax_number" value="{{ old('tax_number', $client->tax_number) }}" class="field h-11 w-full px-3.5"></x-form.field>
    <x-form.field label="Matični broj"><input name="registration_number" value="{{ old('registration_number', $client->registration_number) }}" class="field h-11 w-full px-3.5"></x-form.field>
    <x-form.field label="Adresa" :full="true"><input name="address" value="{{ old('address', $client->address) }}" class="field h-11 w-full px-3.5"></x-form.field>
</x-form.section>

<x-form.section title="Naplata" desc="Podrazumevana satnica i valuta za sve projekte ovog klijenta.">
    <x-form.field label="Cena po satu" :required="true" suffix="/h"><input name="default_hourly_rate" type="number" step="0.01" value="{{ old('default_hourly_rate', $client->default_hourly_rate ?: 20) }}" class="field h-11 w-full pl-3.5 pr-10" required></x-form.field>
    <x-form.field label="Valuta" :required="true"><input name="currency" maxlength="3" value="{{ old('currency', $client->currency ?: 'EUR') }}" class="field h-11 w-full px-3.5 uppercase" required></x-form.field>
    <x-form.field label="Napomene" :full="true"><textarea name="notes" rows="3" class="field w-full px-3.5 py-3">{{ old('notes', $client->notes) }}</textarea></x-form.field>
    <x-form.toggle name="is_active" label="Aktivan klijent" desc="Neaktivni klijenti ostaju u evidenciji." :checked="old('is_active', $client->exists ? $client->is_active : true)" />
</x-form.section>

@unless($client->exists)
<x-form.section title="Nalog za klijenta" desc="Opciono: login za klijentski panel. Ako lozinka ostane prazna, koristi se podrazumevana.">
    <x-form.field label="Ime"><input name="user_name" value="{{ old('user_name') }}" class="field h-11 w-full px-3.5"></x-form.field>
    <x-form.field label="Email za login"><input name="user_email" type="email" value="{{ old('user_email') }}" class="field h-11 w-full px-3.5"></x-form.field>
    <x-form.field label="Lozinka" :full="true"><input name="user_password" placeholder="password123 ako ostane prazno" class="field h-11 w-full px-3.5"></x-form.field>
</x-form.section>
@endunless

<x-form.actions :back="$client->exists ? route('clients.show', $client) : route('clients.index')" />
