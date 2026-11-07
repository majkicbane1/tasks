<x-layouts.app title="Podešavanja" active="settings" eyebrow="Sistem">
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">
        <section class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 p-5 sm:p-6">
                <div>
                    <h2 class="text-lg font-semibold">Migracije baze</h2>
                    <p class="text-sm text-slate-500">Posle deploy-a pokreni migracije ovde. Pokreću se samo one koje još nisu izvršene.</p>
                </div>
                @if($pending > 0)
                    <span class="pill pill-amber">{{ $pending }} na čekanju</span>
                @else
                    <span class="pill pill-green">Baza je ažurna</span>
                @endif
            </div>

            @if($dbError)
                <div class="mx-5 mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 sm:mx-6">Nema veze sa bazom: {{ $dbError }}</div>
            @endif

            <div class="divide-y divide-slate-100 border-t border-slate-100">
                @foreach($migrations as $m)
                    <div class="flex items-center justify-between gap-3 px-5 py-3 sm:px-6">
                        <span class="min-w-0 truncate font-mono text-sm {{ $m['ran'] ? 'text-slate-500' : 'font-medium text-slate-900' }}">{{ $m['name'] }}</span>
                        @if($m['ran'])
                            <span class="pill pill-green shrink-0">Izvršena @if($m['batch'])· batch {{ $m['batch'] }}@endif</span>
                        @else
                            <span class="pill pill-amber shrink-0">Čeka</span>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 bg-slate-50/60 p-4 sm:px-6">
                <form method="POST" action="{{ route('platform.settings.migrate') }}" onsubmit="return confirm('Pokrenuti migracije na ovoj bazi?')">
                    @csrf
                    <button class="btn btn-primary" @disabled($pending === 0)>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3v18l15-9z"/></svg>
                        Pokreni migracije
                    </button>
                </form>
                <span class="text-sm text-slate-500">{{ $pending === 0 ? 'Nema šta da se pokrene.' : 'Pokrenuće se '.$pending.' migracija.' }}</span>
            </div>
        </section>

        <div class="space-y-5">
            <section class="card p-5 sm:p-6">
                <h2 class="text-lg font-semibold">Sistem</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    @foreach($env as $label => $value)
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ $label }}</dt><dd class="text-right font-medium">{{ $value }}</dd></div>
                    @endforeach
                </dl>
            </section>

            <section class="card p-5 sm:p-6">
                <h2 class="text-lg font-semibold">Keš</h2>
                <p class="mt-1 text-sm text-slate-500">Briše keš konfiguracije, ruta i prikaza. Korisno posle deploy-a.</p>
                <form method="POST" action="{{ route('platform.settings.cache') }}" class="mt-4">
                    @csrf
                    <button class="btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 15-6.7L21 8M21 3v5h-5M21 12a9 9 0 0 1-15 6.7L3 16M3 21v-5h5"/></svg>
                        Očisti keš
                    </button>
                </form>
            </section>
        </div>
    </div>

    @if(session('output'))
        <section class="card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-3.5 text-sm font-semibold sm:px-6">Izlaz komande</div>
            <pre class="max-h-96 overflow-auto bg-slate-950 p-5 font-mono text-xs leading-relaxed text-slate-100 sm:px-6">{{ session('output') }}</pre>
        </section>
    @endif
</x-layouts.app>
