<x-layouts.app title="Nova uplata" active="clients" eyebrow="Izaberi klijenta">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @forelse($clients as $client)
            <a href="{{ route('payments.create', ['client_id' => $client->id]) }}" class="card group flex items-center gap-4 p-5 transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-teal-50 text-lg font-semibold text-teal-600">{{ mb_strtoupper(mb_substr($client->company_name, 0, 1)) }}</span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate font-semibold">{{ $client->company_name }}</span>
                    <span class="block truncate text-sm text-slate-500">{{ $client->email ?: 'bez emaila' }}</span>
                </span>
                <span class="text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-teal-600">→</span>
            </a>
        @empty
            <p class="text-sm text-slate-500">Prvo dodaj klijenta.</p>
        @endforelse
    </div>
</x-layouts.app>
