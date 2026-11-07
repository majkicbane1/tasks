<x-layouts.app title="Firma" active="firm" eyebrow="Podešavanja">
    <form method="POST" action="{{ route('firm.update') }}" enctype="multipart/form-data" class="mx-auto w-full max-w-4xl space-y-5">
        @csrf
        @method('PATCH')
        <x-form.section title="Podaci firme" desc="Naziv se prikazuje u meniju.">
            <x-form.field label="Naziv" full required>
                <input name="name" value="{{ old('name', $tenant->name) }}" class="w-full field px-3.5 py-2.5 text-sm" required>
            </x-form.field>
        </x-form.section>

        <x-form.section title="Logo i ikonica" desc="Prikazuju se u meniju tebi i tvojim klijentima, i kao ikonica u tabu pregledača.">
            @include('partials.branding-fields', ['tenant' => $tenant])
        </x-form.section>

        <div class="flex justify-end"><button class="btn btn-primary px-6">Sačuvaj</button></div>
    </form>
</x-layouts.app>
