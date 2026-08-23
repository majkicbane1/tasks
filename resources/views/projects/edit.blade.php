<x-layouts.app :title="'Izmena: '.$project->name" active="projects">
    <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('projects.partials.form')
    </form>
</x-layouts.app>
