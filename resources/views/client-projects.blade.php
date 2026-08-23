<x-layouts.app :title="$client->company_name" active="projects" eyebrow="Projekti">
    <section class="rounded-lg border border-slate-200 bg-white">
        <div class="border-b border-slate-200 p-4"><h2 class="font-semibold">Projekti</h2></div>
        <div class="divide-y divide-slate-100">
            @forelse($projects as $project)
                <a href="{{ route('projects.show', $project) }}" class="grid gap-2 p-4 hover:bg-slate-50 sm:grid-cols-[1fr_auto]">
                    <div>
                        <div class="font-medium">{{ $project->name }}</div>
                        <div class="text-sm text-slate-500">{{ ucfirst($project->status) }}</div>
                    </div>
                    <div class="font-semibold">{{ $money($project->balance, $client->currency) }}</div>
                </a>
            @empty
                <p class="p-4 text-sm text-slate-500">Nema projekata.</p>
            @endforelse
        </div>
    </section>
</x-layouts.app>
