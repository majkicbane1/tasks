<section class="rounded-lg border border-slate-200 bg-white p-4">
    <div class="grid gap-4 md:grid-cols-2">
        <label class="block"><span class="mb-1 block text-sm font-medium">Klijent</span><select id="project_client_id" name="client_id" class="w-full rounded-lg border border-slate-300 px-3 py-2.5" required>@foreach($clients as $client)<option value="{{ $client->id }}" data-hourly-rate="{{ $client->default_hourly_rate }}" @selected((int) old('client_id', request('client_id', $project->client_id)) === $client->id)>{{ $client->company_name }}</option>@endforeach</select></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Naziv projekta</span><input name="name" value="{{ old('name', $project->name) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5" required></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Status</span><select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"><option value="active" @selected(old('status', $project->status) === 'active')>Aktivan</option><option value="paused" @selected(old('status', $project->status) === 'paused')>Pauziran</option><option value="completed" @selected(old('status', $project->status) === 'completed')>Završen</option></select></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Satnica za projekat</span><input id="project_hourly_rate" name="hourly_rate" type="number" step="0.01" value="{{ old('hourly_rate', $project->hourly_rate) }}" placeholder="koristi satnicu klijenta" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Početak</span><input name="started_at" type="date" value="{{ old('started_at', optional($project->started_at)->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block"><span class="mb-1 block text-sm font-medium">Kraj</span><input name="finished_at" type="date" value="{{ old('finished_at', optional($project->finished_at)->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></label>
        <label class="block md:col-span-2"><span class="mb-1 block text-sm font-medium">Opis</span><textarea name="description" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">{{ old('description', $project->description) }}</textarea></label>
    </div>
</section>
<div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ $project->exists ? route('projects.show', $project) : route('projects.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center font-semibold">Nazad</a>
    <button class="rounded-lg bg-teal-600 px-4 py-2.5 font-semibold text-white">Sačuvaj</button>
</div>
@unless($project->exists)
    <script>
        (() => {
            const clientSelect = document.getElementById('project_client_id');
            const hourlyInput = document.getElementById('project_hourly_rate');
            if (!clientSelect || !hourlyInput) return;

            const fillHourlyRate = (force = false) => {
                const selected = clientSelect.options[clientSelect.selectedIndex];
                const rate = selected?.dataset.hourlyRate || '';
                if (force || hourlyInput.value === '') {
                    hourlyInput.value = rate;
                }
            };

            fillHourlyRate(true);
            clientSelect.addEventListener('change', () => fillHourlyRate(true));
        })();
    </script>
@endunless
