<x-layouts.app title="Izmena uplate" active="clients" :crumbs="auth()->user()->isSuperAdmin() ? ['Klijenti' => route('clients.index'), $client->company_name => route('clients.show', $client)] : ['Projekti' => route('client.projects')]">
    <form method="POST" action="{{ route('payments.update', $payment) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('payments.form')
    </form>
</x-layouts.app>
