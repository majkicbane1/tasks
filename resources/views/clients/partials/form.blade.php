<section class="rounded-lg border border-slate-200 bg-white p-4">
    <h2 class="mb-4 font-semibold">Podaci o firmi</h2>
    <div class="grid gap-4 md:grid-cols-2">
        <label class="block"><span class="mb-1 block text-sm font-medium">Naziv firme</span><input name="company_name" value="{{ old('company_name', $client->company_name) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5" required></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Kontakt osoba</span><input name="contact_name" value="{{ old('contact_name', $client->contact_name) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Email firme</span><input name="email" type="email" value="{{ old('email', $client->email) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Telefon</span><input name="phone" value="{{ old('phone', $client->phone) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Website</span><input name="website" value="{{ old('website', $client->website) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">PIB / ID</span><input name="tax_number" value="{{ old('tax_number', $client->tax_number) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Maticni broj</span><input name="registration_number" value="{{ old('registration_number', $client->registration_number) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block md:col-span-2"><span class="mb-1 block text-sm font-medium">Adresa</span><input name="address" value="{{ old('address', $client->address) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Cena po satu</span><input name="default_hourly_rate" type="number" step="0.01" value="{{ old('default_hourly_rate', $client->default_hourly_rate ?: 20) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5" required></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Valuta</span><input name="currency" maxlength="3" value="{{ old('currency', $client->currency ?: 'EUR') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 uppercase" required></label>
        <label class="flex items-center gap-2 md:col-span-2"><input name="is_active" value="1" type="checkbox" class="rounded border-slate-300" @checked(old('is_active', $client->exists ? $client->is_active : true))> Aktivan klijent</label>
        <label class="block md:col-span-2"><span class="mb-1 block text-sm font-medium">Napomene</span><textarea name="notes" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">{{ old('notes', $client->notes) }}</textarea></label>
    </div>
</section>
@unless($client->exists)
<section class="rounded-lg border border-slate-200 bg-white p-4">
    <h2 class="mb-4 font-semibold">Nalog za klijenta</h2>
    <div class="grid gap-4 md:grid-cols-3">
        <label class="block"><span class="mb-1 block text-sm font-medium">Ime</span><input name="user_name" value="{{ old('user_name') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Email za login</span><input name="user_email" type="email" value="{{ old('user_email') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Lozinka</span><input name="user_password" placeholder="password123 ako ostane prazno" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
    </div>
</section>
@endunless
<div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ $client->exists ? route('clients.show', $client) : route('clients.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center font-semibold">Nazad</a>
    <button class="rounded-lg bg-teal-600 px-4 py-2.5 font-semibold text-white">Sačuvaj</button>
</div>
