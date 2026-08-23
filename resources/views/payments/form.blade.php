<input type="hidden" name="client_id" value="{{ old('client_id', $client->id) }}">
<section class="rounded-lg border border-slate-200 bg-white p-4">
    <div class="grid gap-4 md:grid-cols-2">
        <label class="block"><span class="mb-1 block text-sm font-medium">Datum</span><input name="paid_on" type="date" value="{{ old('paid_on', optional($payment->paid_on)->format('Y-m-d') ?: now()->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5" required></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Tip</span><select name="type" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"><option value="payment" @selected(old('type', $payment->type) === 'payment')>Uplata / racun</option><option value="expense" @selected(old('type', $payment->type) === 'expense')>Trosak koji se dodaje</option></select></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Iznos</span><input name="amount" type="number" step="0.01" value="{{ old('amount', $payment->amount) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5" required></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Projekat</span><select name="project_id" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"><option value="">Opšte za klijenta</option>@foreach($client->projects as $project)<option value="{{ $project->id }}" @selected((int) old('project_id', $payment->project_id) === $project->id)>{{ $project->name }}</option>@endforeach</select></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Metod</span><input name="method" value="{{ old('method', $payment->method) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Referenca</span><input name="reference" value="{{ old('reference', $payment->reference) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block md:col-span-2"><span class="mb-1 block text-sm font-medium">Napomena</span><textarea name="note" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">{{ old('note', $payment->note) }}</textarea></label>
        <label class="flex items-center gap-2 md:col-span-2"><input name="invoice_issued" value="1" type="checkbox" class="rounded border-slate-300" @checked(old('invoice_issued', $payment->exists && $payment->status === 'pending_invoice'))> Izdat racun, ceka se placanje</label>
        <label class="flex items-center gap-2 md:col-span-2"><input name="visible_to_client" value="1" type="checkbox" class="rounded border-slate-300" @checked(old('visible_to_client', $payment->exists ? $payment->visible_to_client : true))> Vidi se klijentu</label>
    </div>
</section>
<div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('clients.show', $client) }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center font-semibold">Nazad</a>
    <button class="rounded-lg bg-teal-600 px-4 py-2.5 font-semibold text-white">Sačuvaj</button>
</div>
