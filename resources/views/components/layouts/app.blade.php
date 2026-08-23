@php
    $user = auth()->user();
    $active = $active ?? '';
@endphp
<!doctype html>
<html lang="sr" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Uizzard Poslovi' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans text-slate-900">
    <div class="min-h-screen lg:flex">
        <div id="mobileOverlay" class="fixed inset-0 z-30 hidden bg-slate-950/40 lg:hidden"></div>
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-slate-200 bg-white p-4 transition lg:static lg:translate-x-0">
            <a href="{{ route('dashboard') }}" class="mb-6 flex items-center gap-3 px-2">
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-teal-600 text-base font-bold text-white">UP</span>
                <span>
                    <span class="block text-base font-semibold">Uizzard Poslovi</span>
                    <span class="text-xs text-slate-500">evidencija i naplata</span>
                </span>
            </a>
            <nav class="space-y-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ $active === 'dashboard' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span class="text-lg">D</span> Dashboard
                </a>
                @if($user?->isSuperAdmin())
                    <a href="{{ route('clients.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ $active === 'clients' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-100' }}">
                        <span class="text-lg">K</span> Klijenti
                    </a>
                    <a href="{{ route('projects.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ $active === 'projects' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-100' }}">
                        <span class="text-lg">P</span> Projekti
                    </a>
                @else
                    <a href="{{ route('client.projects') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ $active === 'projects' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-100' }}">
                        <span class="text-lg">P</span> Projekti
                    </a>
                @endif
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="absolute bottom-4 left-4 right-4">
                @csrf
                <div class="mb-3 rounded-lg bg-slate-50 p-3 text-sm">
                    <div class="font-medium">{{ $user?->name }}</div>
                    <div class="break-all text-xs text-slate-500">{{ $user?->email }}</div>
                </div>
                <button class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Odjavi se</button>
            </form>
        </aside>
        <main class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur lg:px-8">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">{{ $eyebrow ?? 'Panel' }}</p>
                        <h1 class="text-xl font-semibold text-slate-950 sm:text-2xl">{{ $title ?? 'Dashboard' }}</h1>
                    </div>
                    <button id="menuButton" class="grid h-10 w-10 place-items-center rounded-lg border border-slate-200 text-slate-700 lg:hidden">Menu</button>
                    <div class="hidden text-right text-sm text-slate-500 lg:block">{{ now()->format('d.m.Y.') }}</div>
                </div>
            </header>
            <div class="p-4 lg:p-8">
                @if(session('status'))
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>
                @endif
                {{ $slot }}
            </div>
        </main>
    </div>
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileOverlay');
        document.getElementById('menuButton')?.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        });
        overlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    </script>
</body>
</html>
