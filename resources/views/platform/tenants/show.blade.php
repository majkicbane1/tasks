<x-layouts.app :title="$tenant->name" active="tenants" eyebrow="Firma">
    @php
        $logo = $tenant->logoUrl('light');
        $inputCls = 'w-full field px-3 py-2.5 text-sm';
        $boxes = '<div class="flex flex-wrap gap-x-5 gap-y-1.5 text-xs text-slate-600">
            <label class="flex items-center gap-2"><input type="checkbox" name="temporary" value="1" checked class="rounded border-slate-300"> Jednokratna lozinka (traži promenu pri prijavi)</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="send_email" value="1" checked class="rounded border-slate-300"> Pošalji podatke mejlom</label>
        </div>';
    @endphp

    {{-- identity + numbers --}}
    <div class="grid gap-4 lg:grid-cols-[minmax(0,1.4fr)_repeat(3,minmax(0,1fr))]">
        <div class="card flex items-center gap-4 p-5">
            <span class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-2xl bg-teal-50 text-2xl font-semibold text-teal-600">
                @if($logo)<img src="{{ $logo }}" alt="" class="max-h-full max-w-full object-contain p-1.5">@else{{ mb_strtoupper(mb_substr($tenant->name, 0, 1)) }}@endif
            </span>
            <div class="min-w-0">
                <h2 class="truncate text-xl font-semibold">{{ $tenant->name }}</h2>
                <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    @if($tenant->is_active)<span class="pill pill-green">Aktivna</span>@else<span class="pill pill-gray">Deaktivirana</span>@endif
                    <span>Od {{ $tenant->created_at->format('d. m. Y.') }}</span>
                </div>
            </div>
        </div>
        @foreach($counts as $label => $count)
            <div class="card flex flex-col justify-center p-5">
                <div class="text-sm text-slate-500">{{ $label }}</div>
                <div class="mt-1 text-3xl font-semibold tracking-tight">{{ $count }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        {{-- left: details + branding --}}
        <form method="POST" action="{{ route('platform.tenants.update', $tenant) }}" enctype="multipart/form-data" class="card space-y-6 p-5 sm:p-6">
            @csrf
            @method('PATCH')
            <div>
                <h2 class="text-base font-semibold">Podaci firme</h2>
                <p class="mt-1 text-sm text-slate-500">Deaktivirana firma ne može da se prijavi, a podaci ostaju sačuvani.</p>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.field label="Naziv" required>
                    <input name="name" value="{{ old('name', $tenant->name) }}" class="w-full field px-3.5 py-2.5 text-sm" required>
                </x-form.field>
                <x-form.toggle name="is_active" label="Firma je aktivna" desc="Isključi da blokiraš sve naloge." :checked="$tenant->is_active" />
            </div>

            <div class="border-t border-slate-100 pt-6">
                <h2 class="text-base font-semibold">Izgled</h2>
                <p class="mb-4 mt-1 text-sm text-slate-500">Logo i ikonica se prikazuju u meniju firme i njenim klijentima.</p>
                @include('partials.branding-fields', ['tenant' => $tenant])
            </div>

            <div class="flex justify-end border-t border-slate-100 pt-5"><button class="btn btn-primary px-6">Sačuvaj izmene</button></div>
        </form>

        {{-- right: admins + danger --}}
        <div class="space-y-5">
            <section class="card">
                <div class="flex items-center justify-between border-b border-slate-100 p-5">
                    <h2 class="text-base font-semibold">Admini firme</h2>
                    <span class="chip bg-surface text-slate-600 ring-1 ring-slate-200">{{ $admins->count() }}</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($admins as $admin)
                        <div class="space-y-3 p-5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="grid h-9 w-9 place-items-center rounded-full bg-gradient-to-br from-teal-300 to-teal-600 text-xs font-semibold text-white">{{ mb_strtoupper(mb_substr($admin->name, 0, 1)) }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-medium">{{ $admin->name }}</div>
                                    <div class="truncate text-xs text-slate-500">{{ $admin->email }}</div>
                                </div>
                                @if($admin->must_change_password)<span class="pill pill-amber">Čeka promenu lozinke</span>@endif
                                @if($admin->hasTwoFactor())<span class="pill pill-green">2FA</span>@endif
                                @if($admin->google_id)<span class="pill pill-gray">Google</span>@endif
                            </div>

                            <details>
                                <summary class="cursor-pointer text-sm font-medium text-teal-700 hover:underline">Izmeni / resetuj lozinku</summary>
                                <form method="POST" action="{{ route('platform.tenants.admins.update', [$tenant, $admin]) }}" class="mt-3 grid gap-3 sm:grid-cols-2">
                                    @csrf
                                    @method('PATCH')
                                    <label class="block"><span class="mb-1 block text-xs font-medium text-slate-600">Ime</span><input name="name" value="{{ $admin->name }}" class="{{ $inputCls }}" required></label>
                                    <label class="block"><span class="mb-1 block text-xs font-medium text-slate-600">Email</span><input name="email" type="email" value="{{ $admin->email }}" class="{{ $inputCls }}" required></label>
                                    <label class="block sm:col-span-2"><span class="mb-1 block text-xs font-medium text-slate-600">Nova lozinka</span><input name="password" type="password" minlength="8" placeholder="ostavi prazno da ne menjaš" autocomplete="new-password" class="{{ $inputCls }}"></label>
                                    <div class="sm:col-span-2">{!! $boxes !!}</div>
                                    <div class="sm:col-span-2"><button class="btn btn-primary">Sačuvaj</button></div>
                                </form>
                                @if($admins->count() > 1)
                                    <form method="POST" action="{{ route('platform.tenants.admins.destroy', [$tenant, $admin]) }}" class="mt-2" onsubmit="return confirm('Obrisati ovog admina?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm font-semibold text-rose-700">Obriši admina</button>
                                    </form>
                                @endif
                            </details>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-slate-100 p-5">
                    <p class="mb-3 text-sm font-medium">Dodaj admina</p>
                    <form method="POST" action="{{ route('platform.tenants.admins.store', $tenant) }}" class="grid gap-3 sm:grid-cols-2">
                        @csrf
                        <label class="block"><span class="mb-1 block text-xs font-medium text-slate-600">Ime</span><input name="name" class="{{ $inputCls }}" required></label>
                        <label class="block"><span class="mb-1 block text-xs font-medium text-slate-600">Email</span><input name="email" type="email" class="{{ $inputCls }}" required></label>
                        <label class="block sm:col-span-2"><span class="mb-1 block text-xs font-medium text-slate-600">Lozinka</span><input name="password" type="password" minlength="8" autocomplete="new-password" class="{{ $inputCls }}" required></label>
                        <div class="sm:col-span-2">{!! $boxes !!}</div>
                        <div class="sm:col-span-2"><button class="btn">Dodaj admina</button></div>
                    </form>
                </div>
            </section>

            <section class="card border-rose-200 p-5">
                <h2 class="text-base font-semibold text-rose-700">Brisanje firme</h2>
                <p class="mt-1 text-sm text-slate-500">Briše firmu i sve u njoj: klijente, projekte, stavke, uplate, odmor i naloge. Ne može da se vrati.</p>
                <form method="POST" action="{{ route('platform.tenants.destroy', $tenant) }}" class="mt-4 space-y-3" onsubmit="return confirm('Trajno obrisati firmu i sve njene podatke?')">
                    @csrf
                    @method('DELETE')
                    <label class="block"><span class="mb-1 block text-xs font-medium text-slate-600">Ukucaj „{{ $tenant->name }}“ za potvrdu</span><input name="confirm" class="{{ $inputCls }}" required autocomplete="off"></label>
                    <button class="btn !text-rose-700">Obriši firmu</button>
                </form>
            </section>
        </div>
    </div>
</x-layouts.app>
