@props(['back'])
<div class="sticky bottom-3 z-10 flex items-center justify-end gap-2 rounded-2xl border border-slate-100 bg-surface/90 p-3 shadow-[0_10px_30px_-12px_rgb(20_20_26/.25)] backdrop-blur">
    <a href="{{ $back }}" class="btn">Nazad</a>
    <button class="btn btn-primary px-6">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Sačuvaj
    </button>
</div>
