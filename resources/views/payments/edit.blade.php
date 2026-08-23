<x-layouts.app title="Izmena uplate" active="clients" :eyebrow="$client->company_name">
    <form method="POST" action="{{ route('payments.update', $payment) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('payments.form')
    </form>
</x-layouts.app>
