<x-layouts.app title="Klijenti" active="clients">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('clients.create') }}" class="rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white">+ Novi klijent</a>
    </div>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <div class="hidden grid-cols-[1fr_120px_150px_150px] gap-4 border-b border-slate-200 bg-slate-50 p-3 text-xs font-semibold uppercase text-slate-500 md:grid">
            <div>Firma</div><div>Projekti</div><div>Satnica</div><div class="text-right">Preostalo</div>
        </div>
        @forelse($clients as $client)
            <a href="{{ route('clients.show', $client) }}" class="grid gap-2 border-b border-slate-100 p-4 last:border-b-0 hover:bg-slate-50 md:grid-cols-[1fr_120px_150px_150px] md:items-center">
                <div><div class="font-medium">{{ $client->company_name }}</div><div class="text-sm text-slate-500">{{ $client->contact_name ?: 'Bez kontakta' }} · {{ $client->email ?: 'bez emaila' }}</div></div>
                <div class="text-sm">{{ $client->projects_count }}</div>
                <div class="text-sm">{{ $money($client->default_hourly_rate, $client->currency) }}/h</div>
                <div class="font-semibold md:text-right {{ $client->balance > 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ $money($client->balance, $client->currency) }}</div>
            </a>
        @empty
            <p class="p-4 text-sm text-slate-500">Još nema klijenata.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $clients->links() }}</div>
</x-layouts.app>
