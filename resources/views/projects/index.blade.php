<x-layouts.app title="Projekti" active="projects">
    <div class="mb-4 flex justify-end"><a href="{{ route('projects.create') }}" class="rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white">+ Novi projekat</a></div>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        @forelse($projects as $project)
            <a href="{{ route('projects.show', $project) }}" class="grid gap-2 border-b border-slate-100 p-4 last:border-b-0 hover:bg-slate-50 md:grid-cols-[1fr_180px_140px] md:items-center">
                <div><div class="font-medium">{{ $project->name }}</div><div class="text-sm text-slate-500">{{ $project->client->company_name }}</div></div>
                <div class="text-sm">{{ ucfirst($project->status) }}</div>
                <div class="font-semibold md:text-right">{{ $money($project->balance, $project->client->currency) }}</div>
            </a>
        @empty
            <p class="p-4 text-sm text-slate-500">Još nema projekata.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $projects->links() }}</div>
</x-layouts.app>
