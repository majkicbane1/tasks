@php
    $slots = [
        'logo_light' => ['Logo · svetla tema', 'Tamniji logo za svetlu pozadinu.', 'bg-white'],
        'logo_dark' => ['Logo · tamna tema', 'Svetliji logo za tamnu pozadinu. Bez njega važi svetli logo.', 'bg-[#131313]'],
        'favicon' => ['Favicon', 'Kvadratna ikonica za tab pregledača.', 'bg-slate-100'],
    ];
@endphp
<div class="grid gap-4 sm:grid-cols-3 sm:col-span-2">
    @foreach($slots as $slot => [$label, $hint, $bg])
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
            <div>
                <div class="text-sm font-medium text-slate-800">{{ $label }}</div>
                <p class="mt-0.5 text-xs leading-relaxed text-slate-500">{{ $hint }}</p>
            </div>

            <div class="grid h-28 place-items-center overflow-hidden rounded-xl border border-dashed border-slate-300 {{ $bg }} p-3" data-preview-box="{{ $slot }}">
                @if($url = $tenant->brandUrl($slot))
                    <img src="{{ $url }}" alt="" class="max-h-full max-w-full object-contain">
                @else
                    <span class="text-xs text-slate-400">Nema fajla</span>
                @endif
            </div>

            <label class="btn w-full cursor-pointer justify-center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
                <span data-file-label="{{ $slot }}">{{ $tenant->{$slot} ? 'Zameni' : 'Izaberi fajl' }}</span>
                <input type="file" name="{{ $slot }}" accept=".png,.jpg,.jpeg,.webp,.svg,.ico,image/*" data-preview-input="{{ $slot }}" class="sr-only">
            </label>

            @if($tenant->{$slot})
                <label class="flex items-center gap-2 text-xs text-slate-600"><input type="checkbox" name="remove[]" value="{{ $slot }}" class="rounded border-slate-300"> Ukloni trenutni</label>
            @endif
        </div>
    @endforeach
    <p class="text-xs text-slate-500 sm:col-span-3">PNG, SVG, JPG, WebP ili ICO, najviše 1 MB po fajlu.</p>
</div>
<script>
    // live preview of a freshly picked file, before saving
    document.querySelectorAll('[data-preview-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;
            const slot = input.dataset.previewInput;
            const box = document.querySelector(`[data-preview-box="${slot}"]`);
            box.innerHTML = '';
            const img = document.createElement('img');
            img.className = 'max-h-full max-w-full object-contain';
            img.src = URL.createObjectURL(file);
            box.appendChild(img);
            document.querySelector(`[data-file-label="${slot}"]`).textContent = file.name.length > 22 ? file.name.slice(0, 20) + '…' : file.name;
        });
    });
</script>
