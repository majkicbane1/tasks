@php
    $user = auth()->user();
    $active = $active ?? '';
    $isAdmin = $user?->isSuperAdmin();
    $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
        'clients' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
        'projects' => '<path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 17l9 5 9-5"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>',
        'exports' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
    ];
    $nav = $isAdmin
        ? [['dashboard', 'Dashboard', route('dashboard')], ['clients', 'Klijenti', route('clients.index')], ['projects', 'Projekti', route('projects.index')], ['exports', 'Izvoz podataka', route('exports.index')], ['settings', 'Podešavanja', route('settings.index')]]
        : [['dashboard', 'Dashboard', route('dashboard')], ['projects', 'Projekti', route('client.projects')]];
    $initials = collect(explode(' ', trim($user?->name ?? 'U')))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
@endphp
<!doctype html>
<html lang="sr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Uizzard Poslovi' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-[#ececf0] font-sans text-slate-900 antialiased">
    <div class="min-h-screen w-full gap-6 p-3 sm:p-5 lg:flex">
        <div id="mobileOverlay" class="fixed inset-0 z-30 hidden bg-slate-950/40 backdrop-blur-sm lg:hidden"></div>

        <aside id="sidebar" class="card fixed inset-y-3 left-3 z-40 flex w-72 -translate-x-[110%] flex-col p-5 transition duration-300 lg:sticky lg:top-5 lg:h-[calc(100vh-2.5rem)] lg:w-[17rem] lg:shrink-0 lg:translate-x-0">
            <a href="{{ route('dashboard') }}" class="mb-8 flex items-center gap-3 px-1">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br from-teal-400 to-teal-700 text-sm font-bold text-white shadow-[0_6px_16px_-6px_rgb(205_94_64/.7)]">UP</span>
                <span class="text-xl font-semibold tracking-tight">Uizzard</span>
            </a>
            <p class="mb-2 px-2 text-xs font-medium uppercase tracking-wider text-slate-400">Glavni meni</p>
            <nav class="space-y-1">
                @foreach($nav as [$key, $label, $href])
                    <a href="{{ $href }}" class="nav-link {{ $active === $key ? 'active' : '' }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$key] !!}</svg>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            @if($isAdmin)
                <p class="mb-2 mt-8 px-2 text-xs font-medium uppercase tracking-wider text-slate-400">Brze akcije</p>
                <nav class="space-y-1">
                    <a href="{{ route('clients.create') }}" class="nav-link"><span class="grid h-5 w-5 place-items-center text-lg leading-none">+</span> Novi klijent</a>
                    <a href="{{ route('projects.create') }}" class="nav-link"><span class="grid h-5 w-5 place-items-center text-lg leading-none">+</span> Novi projekat</a>
                    <a href="{{ route('payments.create') }}" class="nav-link"><span class="grid h-5 w-5 place-items-center text-lg leading-none">+</span> Nova uplata</a>
                </nav>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="mt-auto">
                @csrf
                <div class="mb-3 flex items-center gap-3 rounded-2xl bg-slate-50 p-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-teal-300 to-teal-600 text-sm font-semibold text-white">{{ $initials }}</span>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-medium">{{ $user?->name }}</div>
                        <div class="truncate text-xs text-slate-500">{{ $user?->email }}</div>
                    </div>
                </div>
                <button class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Odjavi se</button>
            </form>
        </aside>

        <main class="min-w-0 flex-1 space-y-5 overflow-x-clip">
            <div class="topfade"><header class="card flex items-center gap-3 p-3 sm:p-4">
                <button id="menuButton" class="icon-btn lg:hidden" aria-label="Meni">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                @if($isAdmin)
                    <form method="GET" action="{{ route('clients.index') }}" class="relative min-w-0 max-w-lg flex-1">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input id="globalSearch" name="q" value="{{ request('q') }}" placeholder="Pretraži klijente…" class="w-full rounded-xl border border-slate-100 bg-slate-50 py-2.5 pl-10 pr-14 text-sm outline-none transition focus:border-teal-300 focus:bg-white">
                        <kbd class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 text-[11px] text-slate-500 sm:block">Ctrl K</kbd>
                    </form>
                @else
                    <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-teal-700">{{ $eyebrow ?? 'Panel' }}</p></div>
                @endif
                <div class="ml-auto hidden text-sm text-slate-500 sm:block">{{ now()->translatedFormat('d. m. Y.') }}</div>
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-teal-300 to-teal-600 text-sm font-semibold text-white">{{ $initials }}</span>
            </header></div>

            <div class="px-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">{{ $eyebrow ?? 'Panel' }}</p>
                <h1 class="text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">{{ $title ?? 'Dashboard' }}</h1>
            </div>

            <div class="space-y-5">
                @if(session('status'))
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>
                @endif
                {{ $slot }}
            </div>
        </main>
    </div>
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileOverlay');
        document.getElementById('menuButton')?.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-[110%]');
            overlay.classList.remove('hidden');
        });
        overlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-[110%]');
            overlay.classList.add('hidden');
        });
    </script>
</body>
</html>
