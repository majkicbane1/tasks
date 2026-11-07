<x-layouts.app title="Firma" active="firm" eyebrow="Podešavanja">
    @php
        $logoLight = $tenant->logoUrl('light');
        $logoDark = $tenant->logoUrl('dark');
        $favicon = $tenant->brandUrl('favicon');
    @endphp

    <form method="POST" action="{{ route('firm.update') }}" enctype="multipart/form-data" class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        @csrf
        @method('PATCH')

        {{-- left: data + files --}}
        <div class="card space-y-6 p-5 sm:p-6">
            <div>
                <h2 class="text-base font-semibold">Podaci firme</h2>
                <p class="mt-1 text-sm text-slate-500">Naziv se prikazuje u meniju ispod logoa.</p>
            </div>
            <x-form.field label="Naziv" required>
                <input name="name" value="{{ old('name', $tenant->name) }}" class="w-full field px-3.5 py-2.5 text-sm" required>
            </x-form.field>

            <div class="border-t border-slate-100 pt-6">
                <h2 class="text-base font-semibold">Logo i ikonica</h2>
                <p class="mb-4 mt-1 text-sm text-slate-500">Prikazuju se tebi i tvojim klijentima, a favicon kao ikonica u tabu pregledača.</p>
                @include('partials.branding-fields', ['tenant' => $tenant])
            </div>

            <div class="flex justify-end border-t border-slate-100 pt-5"><button class="btn btn-primary px-6">Sačuvaj</button></div>
        </div>

        {{-- right: live preview --}}
        <div class="card space-y-5 p-5 sm:p-6 xl:sticky xl:top-5" id="brandPreview" data-light="{{ $logoLight }}" data-dark="{{ $logoDark }}">
            <div>
                <h2 class="text-base font-semibold">Pregled</h2>
                <p class="mt-1 text-sm text-slate-500">Ovako izgleda meni u obe teme. Menja se čim izabereš fajl.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                @foreach(['light' => ['Svetla tema', 'bg-white text-slate-900 border-slate-200', 'bg-slate-100'], 'dark' => ['Tamna tema', 'bg-[#131313] text-white border-[#2a2a2a]', 'bg-white/10']] as $mode => [$label, $cls, $bar])
                    <div>
                        <div class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</div>
                        <div class="rounded-2xl border p-4 {{ $cls }}">
                            <div class="flex h-10 items-center" data-mock="{{ $mode }}">
                                @if(($mode === 'light' ? $logoLight : $logoDark))
                                    <img src="{{ $mode === 'light' ? $logoLight : $logoDark }}" alt="" class="max-h-10 max-w-full object-contain">
                                @else
                                    <span class="text-2xl font-bold tracking-tight">uizzard</span>
                                @endif
                            </div>
                            <p class="mb-4 mt-1 truncate text-xs opacity-60" data-mock-name>{{ $tenant->name }}</p>
                            <div class="space-y-2">
                                <div class="h-8 rounded-xl {{ $bar }}"></div>
                                <div class="h-8 w-4/5 rounded-xl {{ $bar }} opacity-60"></div>
                                <div class="h-8 w-3/5 rounded-xl {{ $bar }} opacity-40"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div>
                <div class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">Tab pregledača</div>
                <div class="flex items-center gap-2 rounded-t-xl border border-b-0 border-slate-200 bg-slate-100 px-3 py-2 text-sm">
                    <span class="grid h-4 w-4 shrink-0 place-items-center overflow-hidden" data-mock="favicon">
                        @if($favicon)<img src="{{ $favicon }}" alt="" class="h-4 w-4 object-contain">@else<span class="h-3.5 w-3.5 rounded-sm bg-slate-300"></span>@endif
                    </span>
                    <span class="truncate" data-mock-name>{{ $tenant->name }}</span>
                </div>
            </div>
        </div>
    </form>

    <script>
        (() => {
            const root = document.getElementById('brandPreview');
            const state = { logo_light: root.dataset.light || null, logo_dark: root.dataset.dark || null };
            const draw = (box, url, cls, fallback) => {
                box.innerHTML = '';
                if (url) {
                    const img = document.createElement('img');
                    img.src = url;
                    img.className = cls;
                    box.appendChild(img);
                } else {
                    box.insertAdjacentHTML('beforeend', fallback);
                }
            };
            const render = () => {
                draw(root.querySelector('[data-mock="light"]'), state.logo_light || state.logo_dark, 'max-h-10 max-w-full object-contain', '<span class="text-2xl font-bold tracking-tight">uizzard</span>');
                draw(root.querySelector('[data-mock="dark"]'), state.logo_dark || state.logo_light, 'max-h-10 max-w-full object-contain', '<span class="text-2xl font-bold tracking-tight">uizzard</span>');
            };
            document.querySelectorAll('[data-preview-input]').forEach((input) => {
                input.addEventListener('change', () => {
                    const file = input.files[0];
                    if (!file) return;
                    const url = URL.createObjectURL(file);
                    const slot = input.dataset.previewInput;
                    if (slot === 'favicon') {
                        draw(root.querySelector('[data-mock="favicon"]'), url, 'h-4 w-4 object-contain', '');
                    } else {
                        state[slot] = url;
                        render();
                    }
                });
            });
            document.querySelector('input[name="name"]').addEventListener('input', (e) => {
                root.querySelectorAll('[data-mock-name]').forEach((n) => (n.textContent = e.target.value));
            });
        })();
    </script>
</x-layouts.app>
