@props(['type', 'clientId' => null, 'format' => 'xlsx'])
<form method="GET" action="{{ route('exports.download', ['type' => $type]) }}" {{ $attributes }}>
    @if($clientId)
        <input type="hidden" name="client_id" value="{{ $clientId }}">
    @endif
    <input type="hidden" name="format" value="{{ $format }}">
    <button class="btn btn-ink">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
        {{ $type === 'all' ? 'Preuzmi sve' : 'Preuzmi' }} · {{ $format === 'csv' ? ($type === 'all' ? 'CSV (ZIP)' : 'CSV') : 'Excel' }}
    </button>
</form>
