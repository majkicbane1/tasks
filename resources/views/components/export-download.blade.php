@props(['type', 'clientId' => null, 'format' => 'xlsx'])
<form method="GET" action="{{ route('exports.download', ['type' => $type]) }}" class="inline-flex items-center rounded-[.95rem] border border-slate-200 bg-surface p-1 shadow-sm">
    @if($clientId)
        <input type="hidden" name="client_id" value="{{ $clientId }}">
    @endif
    <label for="export-format-{{ $type }}" class="sr-only">Format izvoza: {{ $type === 'all' ? 'sve evidencije' : \App\Services\DataExport::TYPES[$type] }}</label>
    <select id="export-format-{{ $type }}" name="format" class="h-9 cursor-pointer rounded-xl bg-transparent pl-3 pr-2 text-sm font-medium text-slate-600 outline-none hover:bg-slate-50">
        <option value="xlsx" @selected($format === 'xlsx')>Excel</option>
        <option value="csv" @selected($format === 'csv')>CSV{{ $type === 'all' ? ' (ZIP)' : '' }}</option>
    </select>
    <button class="btn btn-ink !h-9 !rounded-xl !px-3.5">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
        {{ $type === 'all' ? 'Preuzmi sve' : 'Izvoz' }}
    </button>
</form>
