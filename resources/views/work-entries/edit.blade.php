<x-layouts.app title="Izmena stavke" active="projects" :eyebrow="$project->name">
    <form method="POST" action="{{ route('work-entries.update', $entry) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('work-entries.form')
    </form>
</x-layouts.app>
