@props(['title', 'subtitle' => null])
<!doctype html>
<html lang="sr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | Uizzard Poslovi</title>
    @include('partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-full place-items-center bg-[radial-gradient(60rem_30rem_at_50%_-10%,rgb(42_157_92/.18),transparent)] px-4 py-8 font-sans antialiased">
    <x-theme-toggle class="absolute right-4 top-4" />
    <div class="card w-full max-w-md p-8">
        <div class="mb-6">
            <div class="mb-4 text-3xl font-bold tracking-tight">uizzard</div>
            <h1 class="text-2xl font-semibold">{{ $title }}</h1>
            @if($subtitle)<p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>@endif
        </div>
        @if(session('status'))
            <div class="mb-4 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $errors->first() }}</div>
        @endif
        {{ $slot }}
    </div>
</body>
</html>
