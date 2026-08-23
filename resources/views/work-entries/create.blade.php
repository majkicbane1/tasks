<x-layouts.app title="Nova stavka posla" active="projects" :eyebrow="$project->name">
    <form method="POST" action="{{ route('work-entries.store') }}" class="space-y-6">
        @csrf
        @include('work-entries.form')
    </form>
</x-layouts.app>
