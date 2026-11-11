<x-layouts.app title="Nova stavka posla" active="projects" :crumbs="auth()->user()->isSuperAdmin() ? ['Klijenti' => route('clients.index'), $project->client->company_name => route('clients.show', $project->client), $project->name => route('projects.show', $project)] : ['Projekti' => route('client.projects'), $project->name => route('projects.show', $project)]">
    <form method="POST" action="{{ route('work-entries.store') }}" class="space-y-6">
        @csrf
        @include('work-entries.form')
    </form>
</x-layouts.app>
