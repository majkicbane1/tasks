<x-layouts.auth title="Registracija" subtitle="Napravi svoje okruženje za klijente, projekte i uplate.">
    @php
        // which step holds the first field that failed server-side validation
        $stepOfError = collect(['email' => 1, 'password' => 1, 'name' => 2, 'tenant_name' => 3])
            ->first(fn ($step, $field) => $errors->has($field), 1);
    @endphp

    <div id="regSteps" data-start="{{ $stepOfError }}">
        <div class="mb-6 flex items-center gap-2" aria-hidden="true">
            @foreach([1 => 'Nalog', 2 => 'Ti', 3 => 'Firma'] as $n => $label)
                <div class="flex flex-1 flex-col gap-1.5" data-bar="{{ $n }}">
                    <span class="h-1.5 rounded-full bg-slate-200 transition [.is-done_&]:bg-teal-600"></span>
                    <span class="text-xs font-medium text-slate-500">{{ $n }}. {{ $label }}</span>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('register.store') }}" novalidate>
            @csrf

            <div data-step="1">
                <label class="mb-4 block">
                    <span class="mb-1 block text-sm font-medium">Email</span>
                    <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required>
                </label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium">Lozinka</span>
                        <input name="password" type="password" minlength="8" autocomplete="new-password" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium">Ponovi lozinku</span>
                        <input name="password_confirmation" type="password" minlength="8" autocomplete="new-password" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required>
                    </label>
                </div>
            </div>

            <div data-step="2" hidden>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium">Tvoje ime i prezime</span>
                    <input name="name" value="{{ old('name') }}" autocomplete="name" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required>
                </label>
            </div>

            <div data-step="3" hidden>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium">Naziv firme / freelancera</span>
                    <input name="tenant_name" value="{{ old('tenant_name') }}" class="w-full field px-3 py-2.5 text-base outline-none focus:border-teal-600" required>
                    <span class="mt-1.5 block text-xs text-slate-500">Ovako se zove tvoje okruženje; klijenti i projekti ostaju odvojeni od ostalih firmi.</span>
                </label>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="button" data-back class="btn !h-12 px-5" hidden>Nazad</button>
                <button type="button" data-next class="btn btn-primary !h-12 flex-1">Dalje</button>
                <button type="submit" data-submit class="btn btn-primary !h-12 flex-1" hidden>Napravi nalog</button>
            </div>
        </form>

        @if($googleEnabled)
            <div class="my-5 flex items-center gap-3 text-xs uppercase tracking-wide text-slate-400"><span class="h-px flex-1 bg-slate-200"></span>ili<span class="h-px flex-1 bg-slate-200"></span></div>
            <x-google-button label="Registruj se preko Google-a" />
        @endif
    </div>

    <p class="mt-6 text-center text-sm text-slate-500">Već imaš nalog? <a href="{{ route('login') }}" class="font-medium text-teal-700 hover:underline">Prijavi se</a></p>

    <script>
        (() => {
            const root = document.getElementById('regSteps');
            const steps = [...root.querySelectorAll('[data-step]')];
            const bars = [...root.querySelectorAll('[data-bar]')];
            const back = root.querySelector('[data-back]');
            const next = root.querySelector('[data-next]');
            const submit = root.querySelector('[data-submit]');
            let current = Number(root.dataset.start) || 1;

            const show = (n) => {
                current = n;
                steps.forEach((s) => (s.hidden = Number(s.dataset.step) !== n));
                bars.forEach((b) => b.classList.toggle('is-done', Number(b.dataset.bar) <= n));
                back.hidden = n === 1;
                next.hidden = n === steps.length;
                submit.hidden = n !== steps.length;
                steps[n - 1].querySelector('input')?.focus();
            };

            // check only the fields of the visible step, and that both passwords match
            const valid = () => {
                const inputs = [...steps[current - 1].querySelectorAll('input')];
                const pass = inputs.find((i) => i.name === 'password');
                const confirm = inputs.find((i) => i.name === 'password_confirmation');
                confirm?.setCustomValidity(pass && confirm.value !== pass.value ? 'Lozinke se ne poklapaju.' : '');

                return inputs.every((i) => i.reportValidity());
            };

            next.addEventListener('click', () => valid() && show(current + 1));
            back.addEventListener('click', () => show(current - 1));
            // Enter moves forward instead of submitting half-filled
            root.querySelector('form').addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && current < steps.length) {
                    e.preventDefault();
                    next.click();
                }
            });
            root.querySelector('form').addEventListener('submit', (e) => {
                if (!valid()) e.preventDefault();
            });

            show(current);
        })();
    </script>
</x-layouts.auth>
