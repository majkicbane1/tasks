<!doctype html>
<html lang="sr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title ?? 'Pregled' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-[#ececf0] font-sans text-slate-900 antialiased">
    <main class="mx-auto min-h-screen max-w-7xl space-y-5 p-3 sm:p-5 lg:p-8">
        <header class="card flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
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
