<x-layouts.app title="Novi projekat" active="projects">
    <form method="POST" action="{{ route('projects.store') }}" class="space-y-6">
        @csrf
        @include('projects.partials.form')
    </form>
</x-layouts.app>
