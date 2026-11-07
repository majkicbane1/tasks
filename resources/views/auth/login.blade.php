<!doctype html>
<html lang="sr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prijava | Uizzard Poslovi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-full place-items-center bg-[#ececf0] bg-[radial-gradient(60rem_30rem_at_50%_-10%,#fbe4db,transparent)] px-4 font-sans antialiased">
    <form method="POST" action="{{ route('login.store') }}" class="card w-full max-w-md p-8">
        @csrf
        <div class="mb-6">
            <div class="mb-3 grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-teal-400 to-teal-700 font-bold text-white shadow-[0_6px_16px_-6px_rgb(205_94_64/.7)]">UP</div>
            <h1 class="text-2xl font-semibold">Prijava</h1>
            <p class="mt-1 text-sm text-slate-500">Admin panel za evidenciju poslova, uplata i dugovanja.</p>
        </div>
        @error('email')
            <div class="mb-4 rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $message }}</div>
        @enderror
        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-medium">Email</span>
            <input name="email" type="email" value="{{ old('email') }}" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required autofocus>
        </label>
        <label class="mb-4 block">
            <span class="mb-1 block text-sm font-medium">Lozinka</span>
            <input name="password" type="password" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required>
        </label>
        <label class="mb-5 flex items-center gap-2 text-sm text-slate-600">
            <input name="remember" type="checkbox" value="1" class="rounded border-slate-300 text-teal-600">
            Zapamti me
        </label>
        <button class="btn btn-primary w-full !h-12">Uđi u panel</button>
        <p class="mt-4 text-xs text-slate-500">Demo: admin@uizzard.rs / password123</p>
    </form>
</body>
</html>
