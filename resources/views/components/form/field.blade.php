@props(['label', 'hint' => null, 'full' => false, 'suffix' => null, 'required' => false])
<label class="block {{ $full ? 'sm:col-span-2' : '' }}">
    <span class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}@if($required)<span class="text-teal-600"> *</span>@endif</span>
    @if($suffix)
        <span class="relative block">
            {{ $slot }}
            <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400">{{ $suffix }}</span>
        </span>
    @else
        {{ $slot }}
    @endif
    @if($hint)<span class="mt-1.5 block text-xs text-slate-500">{{ $hint }}</span>@endif
</label>
