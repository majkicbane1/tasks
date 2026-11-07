@props(['title', 'desc' => null])
<section {{ $attributes->class('card grid gap-6 p-5 sm:p-7 lg:grid-cols-[260px_minmax(0,1fr)] lg:gap-12') }}>
    <div>
        <h2 class="text-base font-semibold">{{ $title }}</h2>
        @if($desc)<p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $desc }}</p>@endif
    </div>
    <div class="grid content-start gap-5 sm:grid-cols-2">{{ $slot }}</div>
</section>
