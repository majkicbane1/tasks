<section class="mt-6 rounded-lg border border-slate-200 bg-white">
    @php($showProject = $showProject ?? false)
    <div class="border-b border-slate-200 p-4"><h2 class="font-semibold">Poslovi</h2></div>
    <div class="divide-y divide-slate-100 md:hidden">
        @forelse($entries as $entry)
            <div class="p-4">
                <div class="flex items-start justify-between gap-3"><div><div class="font-medium">{{ $entry->title }}</div><div class="text-sm text-slate-500">{{ $showProject ? ($entry->project?->name ?: '-') : (optional($entry->worked_on)->format('d.m.Y.') ?: '-') }}</div></div><div class="font-semibold">{{ $money($entry->amount, $currency) }}</div></div>
                <div class="mt-2 text-sm text-slate-600">{{ $entry->hours ? number_format((float) $entry->hours, 2, ',', '.') . 'h' : 'Fiksno' }}</div>
                @unless($readonly)<div class="mt-3 flex gap-3"><a class="text-sm font-semibold text-teal-700" href="{{ route('work-entries.edit', $entry) }}">Izmeni</a><form method="POST" action="{{ route('work-entries.destroy', $entry) }}" onsubmit="return confirm('Obrisati stavku posla?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-rose-700">Obrisi</button></form></div>@endunless
            </div>
        @empty
            <p class="p-4 text-sm text-slate-500">Nema unetih poslova.</p>
        @endforelse
    </div>
    <div class="hidden overflow-x-auto md:block">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="p-3">{{ $showProject ? 'Projekat' : 'Datum' }}</th><th class="p-3">Opis</th><th class="p-3 text-right">Sati</th><th class="p-3 text-right">Ukupno</th>@unless($readonly)<th class="p-3"></th>@endunless</tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($entries as $entry)
                    <tr><td class="p-3">{{ $showProject ? ($entry->project?->name ?: '-') : (optional($entry->worked_on)->format('d.m.Y.') ?: '-') }}</td><td class="p-3"><div class="font-medium">{{ $entry->title }}</div><div class="text-xs text-slate-500">{{ $entry->description }}</div></td><td class="p-3 text-right">{{ $entry->hours ?: '-' }}</td><td class="p-3 text-right font-semibold">{{ $money($entry->amount, $currency) }}</td>@unless($readonly)<td class="p-3"><div class="flex justify-end gap-3"><a class="font-semibold text-teal-700" href="{{ route('work-entries.edit', $entry) }}">Izmeni</a><form method="POST" action="{{ route('work-entries.destroy', $entry) }}" onsubmit="return confirm('Obrisati stavku posla?')">@csrf @method('DELETE')<button class="font-semibold text-rose-700">Obrisi</button></form></div></td>@endunless</tr>
                @empty
                    <tr><td colspan="6" class="p-4 text-slate-500">Nema unetih poslova.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
