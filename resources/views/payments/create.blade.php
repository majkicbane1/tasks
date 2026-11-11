<x-layouts.app title="Nova uplata" active="clients" :crumbs="auth()->user()->isSuperAdmin() ? ['Klijenti' => route('clients.index'), $client->company_name => route('clients.show', $client)] : ['Projekti' => route('client.projects')]">
    <form method="POST" action="{{ route('payments.store') }}" class="space-y-6">
        @csrf
        @include('payments.form')
    </form>
</x-layouts.app>
