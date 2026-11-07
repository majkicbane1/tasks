@php
    $allowMarkPaid = $allowMarkPaid ?? true;
    $moreUrl = $moreUrl ?? null;
    $hasMore = $hasMore ?? false;
@endphp
<section class="card overflow-hidden">
    <div class="border-b border-slate-100 p-5"><h2 class="text-lg font-semibold">Uplate i računi</h2></div>
    <div id="payments-list" class="divide-y divide-slate-100">
        @forelse($payments as $payment)
            @include('projects.partials.payment-row')
        @empty
            <p class="p-4 text-sm text-slate-500">Nema uplata.</p>
        @endforelse
    </div>
    @if($moreUrl && $hasMore)
        <div data-more-wrap class="border-t border-slate-100 p-4 text-center">
            <button type="button" class="btn" data-load-more data-url="{{ $moreUrl }}" data-type="payments" data-page="2" data-rows="#payments-list">Učitaj još</button>
        </div>
    @endif
</section>
