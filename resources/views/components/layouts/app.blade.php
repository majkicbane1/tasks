@php
    $user = auth()->user();
    $active = $active ?? '';
    $isAdmin = $user?->isSuperAdmin();
    $isPlatform = $user?->isPlatformAdmin();
    $homeUrl = route($user?->homeRoute() ?? 'dashboard');
    $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
        'clients' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
        'projects' => '<path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 17l9 5 9-5"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>',
        'leave' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'firm' => '<path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/>',
        'tenants' => '<path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/>',
        'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
        'account' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/>',
        'exports' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
    ];
    $nav = match (true) {
        $isPlatform => [['tenants', 'Firme', route('platform.tenants.index')], ['mail', 'Mejl i prijava', route('platform.settings')], ['settings', 'Održavanje', route('platform.settings.index')], ['account', 'Moj nalog', route('account.edit')]],
        $isAdmin => [['dashboard', 'Dashboard', route('dashboard')], ['clients', 'Klijenti', route('clients.index')], ['projects', 'Projekti', route('projects.index')], ['leave', 'Odmor', route('leave.index')], ['exports', 'Izvoz podataka', route('exports.index')], ['firm', 'Firma', route('firm.edit')], ['account', 'Moj nalog', route('account.edit')]],
        default => [['dashboard', 'Dashboard', route('dashboard')], ['projects', 'Projekti', route('client.projects')], ['account', 'Moj nalog', route('account.edit')]],
    };
    $brand = $isPlatform ? null : $user?->tenant;
    $tenantName = $isPlatform ? 'Platforma' : $brand?->name;
    $logoLight = $brand?->logoUrl('light');
    $logoDark = $brand?->logoUrl('dark');
    $favicon = $brand?->brandUrl('favicon');
    $initials = collect(explode(' ', trim($user?->name ?? 'U')))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
@endphp
<!doctype html>
<html lang="sr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Uizzard Poslovi' }}</title>
    @if($favicon)<link rel="icon" href="{{ $favicon }}">@endif
    @include('partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans text-slate-900 antialiased">
    <div class="min-h-screen w-full gap-6 p-3 sm:p-5 lg:flex">
        <div id="mobileOverlay" class="fixed inset-0 z-30 hidden bg-slate-950/40 backdrop-blur-sm lg:hidden"></div>

        <aside id="sidebar" class="card fixed inset-y-3 left-3 z-40 flex w-72 -translate-x-[110%] flex-col p-5 transition duration-300 lg:sticky lg:top-5 lg:h-[calc(100vh-2.5rem)] lg:w-[17rem] lg:shrink-0 lg:translate-x-0">
            <a href="{{ $homeUrl }}" class="block px-2 text-2xl font-bold tracking-tight text-slate-900" aria-label="{{ $tenantName ?: 'uizzard' }}">
                @if($logoLight)
                    <img src="{{ $logoLight }}" alt="{{ $tenantName }}" class="max-h-10 max-w-full object-contain object-left dark:hidden">
                    <img src="{{ $logoDark }}" alt="{{ $tenantName }}" class="hidden max-h-10 max-w-full object-contain object-left dark:block">
                @else
                    uizzard
                @endif
            </a>
            <p class="mb-8 mt-1 truncate px-2 text-xs text-slate-500">{{ $tenantName ?: ' ' }}</p>
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
                    <a href="{{ route('account.edit') }}" class="min-w-0 flex-1" title="Moj nalog">
                        <div class="truncate text-sm font-medium">{{ $user?->name }}</div>
                        <div class="truncate text-xs text-slate-500">{{ $user?->email }}</div>
                    </a>
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
                        <input id="globalSearch" name="q" value="{{ request('q') }}" placeholder="Pretraži klijente…" class="w-full rounded-xl border border-slate-100 bg-slate-50 py-2.5 pl-10 pr-14 text-sm outline-none transition focus:border-teal-300 focus:bg-surface">
                        <kbd class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-md border border-slate-200 bg-surface px-1.5 py-0.5 text-[11px] text-slate-500 sm:block">Ctrl K</kbd>
                    </form>
                @else
                    <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-teal-700">{{ $eyebrow ?? 'Panel' }}</p></div>
                @endif
                <div class="ml-auto hidden text-sm text-slate-500 sm:block">{{ now()->translatedFormat('d. m. Y.') }}</div>
                <x-theme-toggle class="max-sm:ml-auto" />
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-teal-300 to-teal-600 text-sm font-semibold text-white">{{ $initials }}</span>
            </header></div>

            <div class="px-1">
                @if(!empty($crumbs))
                    <nav aria-label="Putanja" class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-sm">
                        @foreach($crumbs as $label => $url)
                            @if(!$loop->first)<svg class="h-3.5 w-3.5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>@endif
                            <a href="{{ $url }}" class="font-medium text-slate-500 transition hover:text-teal-700">{{ $label }}</a>
                        @endforeach
                    </nav>
                @else
                    <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">{{ $eyebrow ?? 'Panel' }}</p>
                @endif
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
