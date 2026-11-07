@php
    $color = $color ?? '#cd5e40';
    $trend = $trend ?? null;
@endphp
<div class="card rise p-5">
    <div class="flex items-center gap-3">
        <span class="grid h-10 w-10 place-items-center rounded-xl" style="background: {{ $color }}1a; color: {{ $color }}">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icon !!}</svg>
        </span>
        <span class="font-medium text-slate-700">{{ $label }}</span>
    </div>
    <div class="mt-4 flex items-end justify-between gap-2">
        <div class="min-w-0">
            <div class="truncate text-2xl font-semibold tracking-tight xl:text-[1.7rem]">{{ $value }}</div>
            <div class="mt-3 flex items-center gap-2 text-xs text-slate-500">
                @if($trend !== null)
                    <span class="chip {{ ($trendGood ?? true) ? ($trend >= 0 ? 'chip-down' : 'chip-up') : ($trend >= 0 ? 'chip-up' : 'chip-down') }}">{{ $trend >= 0 ? '↑' : '↓' }} {{ abs($trend) }}%</span>
                    od prošlog meseca
                @else
                    {{ $hint ?? '' }}
                @endif
            </div>
        </div>
        @isset($spark)<div data-chart="spark" data-values='@json($spark)' data-color="{{ $color }}" class="shrink-0"></div>@endisset
    </div>
</div>
