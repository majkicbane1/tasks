@props(['type', 'clientId' => null, 'format' => 'xlsx'])
<form method="GET" action="{{ route('exports.download', ['type' => $type]) }}" class="flex flex-wrap items-center gap-2">
    @if($clientId)
        <input type="hidden" name="client_id" value="{{ $clientId }}">
    @endif
    <label for="export-format-{{ $type }}" class="sr-only">Format izvoza: {{ $type === 'all' ? 'sve evidencije' : \App\Services\DataExport::TYPES[$type] }}</label>
    <select id="export-format-{{ $type }}" name="format" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
        <option value="xlsx" @selected($format === 'xlsx')>Excel (.xlsx)</option>
        <option value="csv" @selected($format === 'csv')>CSV{{ $type === 'all' ? ' (ZIP)' : '' }}</option>
    </select>
    <button class="rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white">{{ $type === 'all' ? 'Preuzmi sve' : 'Preuzmi' }}</button>
</form>
