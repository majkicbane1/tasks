<x-layouts.app :title="'Izmena: '.$client->company_name" active="clients">
    <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('clients.partials.form')
    </form>

    @include('clients.partials.accounts')
</x-layouts.app>
