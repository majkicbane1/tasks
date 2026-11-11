<x-layouts.app :title="'Izmena: '.$project->name" active="projects" :crumbs="['Klijenti' => route('clients.index'), $project->client->company_name => route('clients.show', $project->client), $project->name => route('projects.show', $project)]">
    <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('projects.partials.form')
    </form>
</x-layouts.app>
