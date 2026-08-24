<!doctype html>
<html lang="sr" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title ?? 'Pregled' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans text-slate-900">
    <main class="mx-auto min-h-screen max-w-7xl p-4 lg:p-8">
        <header class="mb-6 flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">{{ $eyebrow ?? 'Podeljeni pregled' }}</p>
                <h1 class="text-2xl font-semibold text-slate-950">{{ $title ?? 'Pregled' }}</h1>
            </div>
            {{ $nav ?? '' }}
        </header>

        {{ $slot }}
    </main>
</body>
</html>
