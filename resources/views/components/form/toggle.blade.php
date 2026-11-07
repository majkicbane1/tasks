@props(['name', 'label', 'desc' => null, 'checked' => false])
<label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3.5 transition hover:bg-slate-50 sm:col-span-2">
    <span>
        <span class="block text-sm font-medium">{{ $label }}</span>
        @if($desc)<span class="block text-xs text-slate-500">{{ $desc }}</span>@endif
    </span>
    <span class="relative inline-flex shrink-0">
        <input type="checkbox" name="{{ $name }}" value="1" class="peer sr-only" @checked($checked)>
        <span class="relative h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-teal-600 peer-focus-visible:ring-4 peer-focus-visible:ring-teal-100 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
    </span>
</label>
