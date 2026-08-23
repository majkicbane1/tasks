<input type="hidden" name="project_id" value="{{ old('project_id', $project->id) }}">
<section class="rounded-lg border border-slate-200 bg-white p-4">
    <div class="grid gap-4 md:grid-cols-2">
        <label class="block"><span class="mb-1 block text-sm font-medium">Datum</span><input name="worked_on" type="date" value="{{ old('worked_on', optional($entry->worked_on)->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Status</span><select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"><option value="billable" @selected(old('status', $entry->status) === 'billable')>Naplativo</option><option value="included" @selected(old('status', $entry->status) === 'included')>Uključeno / gratis</option><option value="draft" @selected(old('status', $entry->status) === 'draft')>Draft</option></select></label>
        <label class="block md:col-span-2"><span class="mb-1 block text-sm font-medium">Naziv / opis posla</span><input name="title" value="{{ old('title', $entry->title) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5" required></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Sati</span><input name="hours" type="number" step="0.01" value="{{ old('hours', $entry->hours) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Cena po satu</span><input name="hourly_rate" type="number" step="0.01" value="{{ old('hourly_rate', $entry->hourly_rate ?: $project->hourly_rate ?: $project->client->default_hourly_rate) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block md:col-span-2"><span class="mb-1 block text-sm font-medium">Fiksni iznos</span><input name="fixed_amount" type="number" step="0.01" value="{{ old('fixed_amount', $entry->fixed_amount) }}" placeholder="ako ovo upišeš, ignoriše se obračun sati" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block md:col-span-2"><span class="mb-1 block text-sm font-medium">Detalji za klijenta</span><textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">{{ old('description', $entry->description) }}</textarea></label>
        <label class="block md:col-span-2"><span class="mb-1 block text-sm font-medium">Interna napomena</span><textarea name="internal_note" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">{{ old('internal_note', $entry->internal_note) }}</textarea></label>
        <label class="flex items-center gap-2 md:col-span-2"><input name="visible_to_client" value="1" type="checkbox" class="rounded border-slate-300" @checked(old('visible_to_client', $entry->exists ? $entry->visible_to_client : true))> Vidi se klijentu</label>
    </div>
</section>
<div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('projects.show', $project) }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center font-semibold">Nazad</a>
    <button class="rounded-lg bg-teal-600 px-4 py-2.5 font-semibold text-white">Sačuvaj</button>
</div>
