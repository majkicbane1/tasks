<x-layouts.app title="Nova uplata" active="clients" :eyebrow="$client->company_name">
    <form method="POST" action="{{ route('payments.store') }}" class="space-y-6">
        @csrf
        @include('payments.form')
    </form>
</x-layouts.app>
