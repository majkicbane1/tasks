<input type="hidden" name="project_id" value="{{ old('project_id', $project->id) }}">
@php $currency = $project->client->currency; @endphp
<x-form.section title="Osnovno" desc="Šta je urađeno, kada i kako se vodi u obračunu.">
    <x-form.field label="Datum"><input name="worked_on" type="date" value="{{ old('worked_on', optional($entry->worked_on)->format('Y-m-d')) }}" class="field h-11 w-full px-3.5"></x-form.field>
    <x-form.field label="Status"><select name="status" class="field h-11 w-full px-3.5"><option value="billable" @selected(old('status', $entry->status) === 'billable')>Naplativo</option><option value="included" @selected(old('status', $entry->status) === 'included')>Uključeno / gratis</option><option value="draft" @selected(old('status', $entry->status) === 'draft')>Draft</option></select></x-form.field>
    <x-form.field label="Naziv posla" :full="true" :required="true"><input name="title" value="{{ old('title', $entry->title) }}" placeholder="npr. Website design" class="field h-11 w-full px-3.5" required></x-form.field>
</x-form.section>

<x-form.section title="Obračun" desc="Sati i satnica, ili fiksni iznos koji ima prednost.">
    <x-form.field label="Sati" suffix="h"><input id="we_hours" name="hours" type="number" step="0.01" value="{{ old('hours', $entry->hours) }}" class="field h-11 w-full pl-3.5 pr-10"></x-form.field>
    <x-form.field label="Cena po satu" :suffix="$currency.'/h'"><input id="we_rate" name="hourly_rate" type="number" step="0.01" value="{{ old('hourly_rate', $entry->hourly_rate ?: $project->hourly_rate ?: $project->client->default_hourly_rate) }}" class="field h-11 w-full pl-3.5 pr-16"></x-form.field>
    <x-form.field label="Fiksni iznos" :full="true" :suffix="$currency" hint="Ako upišeš iznos, obračun po satima se ignoriše."><input id="we_fixed" name="fixed_amount" type="number" step="0.01" value="{{ old('fixed_amount', $entry->fixed_amount) }}" class="field h-11 w-full pl-3.5 pr-14"></x-form.field>
    <div class="flex items-center justify-between rounded-2xl bg-teal-50 px-5 py-4 sm:col-span-2">
        <span class="text-sm font-medium text-teal-800">Ukupno za naplatu</span>
        <span id="we_total" class="text-2xl font-semibold tracking-tight text-teal-700">—</span>
    </div>
</x-form.section>

<x-form.section title="Napomene" desc="Detalji koje klijent vidi i interna beleška samo za tebe.">
    <x-form.field label="Detalji za klijenta" :full="true"><textarea name="description" rows="3" class="field w-full px-3.5 py-3">{{ old('description', $entry->description) }}</textarea></x-form.field>
    <x-form.field label="Interna napomena" :full="true" hint="Ne vidi se klijentu."><textarea name="internal_note" rows="2" class="field w-full px-3.5 py-3">{{ old('internal_note', $entry->internal_note) }}</textarea></x-form.field>
    <x-form.toggle name="visible_to_client" label="Vidi se klijentu" desc="Task je prikazan u klijentskom panelu i share pregledu." :checked="old('visible_to_client', $entry->exists ? $entry->visible_to_client : true)" />
</x-form.section>

<x-form.actions :back="route('projects.show', $project)" />

<script>
    (() => {
        const q = (id) => document.getElementById(id);
        const status = document.querySelector('[name=status]');
        const out = q('we_total');
        const cur = @json($currency);
        const calc = () => {
            const fixed = q('we_fixed').value;
            const amount = status.value !== 'billable' ? 0 : (fixed !== '' ? parseFloat(fixed) : (parseFloat(q('we_hours').value) || 0) * (parseFloat(q('we_rate').value) || 0));
            out.textContent = new Intl.NumberFormat('sr-RS', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount || 0) + ' ' + cur;
        };
        ['we_hours', 'we_rate', 'we_fixed'].forEach((id) => q(id).addEventListener('input', calc));
        status.addEventListener('change', calc);
        calc();
    })();
</script>
