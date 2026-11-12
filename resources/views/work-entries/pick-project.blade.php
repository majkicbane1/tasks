<x-layouts.app title="Novi task" active="projects" eyebrow="Izaberi projekat">
    <div class="space-y-6">
        @forelse($projects as $group)
            <section>
                <h2 class="mb-3 text-sm font-semibold text-slate-500">{{ $group->first()->client->company_name }}</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                    @foreach($group as $project)
                        <a href="{{ route('work-entries.create', ['project_id' => $project->id]) }}" class="card group flex items-center gap-4 p-5 transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-teal-50 text-teal-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 17l9 5 9-5"/></svg></span>
                            <span class="min-w-0 flex-1 truncate font-semibold">{{ $project->name }}</span>
                            <span class="text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-teal-600">→</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @empty
            <p class="text-sm text-slate-500">Prvo dodaj projekat.</p>
        @endforelse
    </div>
</x-layouts.app>
