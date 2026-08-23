<!doctype html>
<html lang="sr" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prijava | Uizzard Poslovi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-full place-items-center px-4 font-sans">
    <form method="POST" action="{{ route('login.store') }}" class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div class="mb-6">
            <div class="mb-3 grid h-12 w-12 place-items-center rounded-lg bg-teal-600 font-bold text-white">UP</div>
            <h1 class="text-2xl font-semibold">Prijava</h1>
            <p class="mt-1 text-sm text-slate-500">Admin panel za evidenciju poslova, uplata i dugovanja.</p>
        </div>
        @error('email')
            <div class="mb-4 rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $message }}</div>
        @enderror
        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-medium">Email</span>
            <input name="email" type="email" value="{{ old('email') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-base outline-none focus:border-teal-600" required autofocus>
        </label>
        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-medium">Lozinka</span>
            <input name="password" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-base outline-none focus:border-teal-600" required>
        </label>
        <label class="mb-5 flex items-center gap-2 text-sm text-slate-600">
            <input name="remember" type="checkbox" value="1" class="rounded border-slate-300 text-teal-600">
            Zapamti me
        </label>
        <button class="w-full rounded-lg bg-teal-600 px-4 py-3 font-semibold text-white hover:bg-teal-700">Uđi u panel</button>
        <p class="mt-4 text-xs text-slate-500">Demo: admin@uizzard.rs / password123</p>
    </form>
</body>
</html>
