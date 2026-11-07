<x-form.section title="Projekat" desc="Za kog klijenta radiš, kako se zove i u kom je statusu.">
    <x-form.field label="Klijent" :required="true"><select id="project_client_id" name="client_id" class="field h-11 w-full px-3.5" required>@foreach($clients as $client)<option value="{{ $client->id }}" data-hourly-rate="{{ $client->default_hourly_rate }}" @selected((int) old('client_id', request('client_id', $project->client_id)) === $client->id)>{{ $client->company_name }}</option>@endforeach</select></x-form.field>
    <x-form.field label="Status"><select name="status" class="field h-11 w-full px-3.5"><option value="active" @selected(old('status', $project->status) === 'active')>Aktivan</option><option value="paused" @selected(old('status', $project->status) === 'paused')>Pauziran</option><option value="completed" @selected(old('status', $project->status) === 'completed')>Završen</option></select></x-form.field>
    <x-form.field label="Naziv projekta" :full="true" :required="true"><input name="name" value="{{ old('name', $project->name) }}" class="field h-11 w-full px-3.5" required></x-form.field>
    <x-form.field label="Opis" :full="true"><textarea name="description" rows="4" class="field w-full px-3.5 py-3">{{ old('description', $project->description) }}</textarea></x-form.field>
</x-form.section>

<x-form.section title="Rok i cena" desc="Period rada i satnica koja važi za ovaj projekat.">
    <x-form.field label="Početak"><input name="started_at" type="date" value="{{ old('started_at', optional($project->started_at)->format('Y-m-d')) }}" class="field h-11 w-full px-3.5"></x-form.field>
    <x-form.field label="Kraj"><input name="finished_at" type="date" value="{{ old('finished_at', optional($project->finished_at)->format('Y-m-d')) }}" class="field h-11 w-full px-3.5"></x-form.field>
    <x-form.field label="Satnica za projekat" :full="true" suffix="/h" hint="Ako ostane prazno, koristi se satnica klijenta."><input id="project_hourly_rate" name="hourly_rate" type="number" step="0.01" value="{{ old('hourly_rate', $project->hourly_rate) }}" class="field h-11 w-full pl-3.5 pr-10"></x-form.field>
</x-form.section>

<x-form.actions :back="$project->exists ? route('projects.show', $project) : route('projects.index')" />
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
