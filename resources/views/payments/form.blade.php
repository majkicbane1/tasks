<input type="hidden" name="client_id" value="{{ old('client_id', $client->id) }}">
<x-form.section title="Uplata" desc="Iznos, datum i vrsta stavke.">
    <x-form.field label="Tip"><select name="type" class="field h-11 w-full px-3.5"><option value="payment" @selected(old('type', $payment->type) === 'payment')>Uplata / račun</option><option value="expense" @selected(old('type', $payment->type) === 'expense')>Trošak koji se dodaje</option></select></x-form.field>
    <x-form.field label="Datum" :required="true"><input name="paid_on" type="date" value="{{ old('paid_on', optional($payment->paid_on)->format('Y-m-d') ?: now()->format('Y-m-d')) }}" class="field h-11 w-full px-3.5" required></x-form.field>
    <x-form.field label="Iznos" :required="true" :suffix="$client->currency"><input name="amount" type="number" step="0.01" value="{{ old('amount', $payment->amount) }}" class="field h-11 w-full pl-3.5 pr-14" required></x-form.field>
    <x-form.field label="Projekat"><select name="project_id" class="field h-11 w-full px-3.5"><option value="">Opšte za klijenta</option>@foreach($client->projects as $project)<option value="{{ $project->id }}" @selected((int) old('project_id', $payment->project_id) === $project->id)>{{ $project->name }}</option>@endforeach</select></x-form.field>
</x-form.section>

<x-form.section title="Detalji" desc="Način plaćanja, broj fakture i napomena.">
    <x-form.field label="Metod"><input name="method" value="{{ old('method', $payment->method) }}" placeholder="npr. Bank transfer" class="field h-11 w-full px-3.5"></x-form.field>
    <x-form.field label="Referenca"><input name="reference" value="{{ old('reference', $payment->reference) }}" placeholder="npr. Faktura 82" class="field h-11 w-full px-3.5"></x-form.field>
    <x-form.field label="Napomena" :full="true"><textarea name="note" rows="3" class="field w-full px-3.5 py-3">{{ old('note', $payment->note) }}</textarea></x-form.field>
    <x-form.toggle name="invoice_issued" label="Izdat račun, čeka se plaćanje" desc="Računa se kao plaćeno tek kad ga označiš kao plaćenog." :checked="old('invoice_issued', $payment->exists && $payment->status === 'pending_invoice')" />
    <x-form.toggle name="visible_to_client" label="Vidi se klijentu" desc="Prikazano u klijentskom panelu i share pregledu." :checked="old('visible_to_client', $payment->exists ? $payment->visible_to_client : true)" />
</x-form.section>

<x-form.actions :back="route('clients.show', $client)" />
