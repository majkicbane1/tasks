<x-layouts.app title="Projekti" active="projects" eyebrow="Evidencija">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <span class="chip bg-white text-slate-700 ring-1 ring-slate-200">{{ $projects->total() }} {{ $projects->total() === 1 ? 'projekat' : 'projekata' }}</span>
        <div class="flex flex-wrap items-center gap-2">
            <x-export-download type="projects" />
            <a href="{{ route('projects.create') }}" class="btn btn-primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg> Novi projekat</a>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="hidden grid-cols-[minmax(0,2fr)_minmax(0,1.2fr)_130px_150px_24px] items-center gap-4 border-b border-slate-100 bg-slate-50/80 px-6 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-500 md:grid">
            <div>Projekat</div><div>Klijent</div><div>Status</div><div class="text-right">Preostalo</div><div></div>
        </div>
        @forelse($projects as $project)
            @php
                $st = ['active' => ['Aktivan', 'pill-green'], 'paused' => ['Pauziran', 'pill-amber'], 'completed' => ['Završen', 'pill-gray']][$project->status] ?? [ucfirst($project->status), 'pill-gray'];
                $balance = $project->balance;
            @endphp
            <a href="{{ route('projects.show', $project) }}" class="group grid gap-2 border-b border-slate-100 px-5 py-4 transition last:border-b-0 hover:bg-slate-50/80 sm:px-6 md:grid-cols-[minmax(0,2fr)_minmax(0,1.2fr)_130px_150px_24px] md:items-center md:gap-4">
                <div class="flex min-w-0 items-center gap-3.5">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-teal-50 text-teal-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 17l9 5 9-5"/></svg>
                    </span>
                    <div class="min-w-0 truncate font-semibold">{{ $project->name }}</div>
                </div>
                <div class="truncate text-sm text-slate-600">{{ $project->client->company_name }}</div>
                <div><span class="pill {{ $st[1] }}">{{ $st[0] }}</span></div>
                <div class="font-semibold md:text-right {{ $balance > 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ $money($balance, $project->client->currency) }}</div>
                <div class="hidden text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-teal-600 md:block">→</div>
            </a>
        @empty
            <div class="p-10 text-center text-sm text-slate-500">Još nema projekata.</div>
        @endforelse
    </div>
    <div>{{ $projects->links() }}</div>
</x-layouts.app>
