<x-layouts.app title="Novi klijent" active="clients">
    <form method="POST" action="{{ route('clients.store') }}" class="space-y-6">
        @csrf
        @include('clients.partials.form')
    </form>
</x-layouts.app>
