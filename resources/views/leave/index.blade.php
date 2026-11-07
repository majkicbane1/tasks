@use('App\Models\LeaveDay')
@php
    $monthLabel = $month->copy()->locale('sr_Latn')->translatedFormat('F Y');
    $dayNames = ['Pon', 'Uto', 'Sre', 'Čet', 'Pet', 'Sub', 'Ned'];
    $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Avg', 'Sep', 'Okt', 'Nov', 'Dec'];
    $maxMonth = max($perMonth->max() ?? 0, 1);
    $today = now()->format('Y-m-d');
    $spark = [];
@endphp
<x-layouts.app title="Odmor" active="leave" eyebrow="Socijalno · Švedska">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        @include('partials.stat-card', ['label' => ucfirst($monthLabel), 'value' => LeaveDay::format($monthTotal).' '.($monthTotal == 1 ? 'dan' : 'dana'), 'color' => '#2fa862', 'hint' => 'Iskorišćeno u ovom mesecu',
            'icon' => '<rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/>'])
        @include('partials.stat-card', ['label' => 'Godina '.$month->year, 'value' => LeaveDay::format($yearTotal).' '.($yearTotal == 1 ? 'dan' : 'dana'), 'color' => '#3b8fd9', 'hint' => 'Ukupno iskorišćeno',
            'icon' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>'])
        <div class="card rise p-5">
            <div class="flex items-center justify-between">
                <span class="font-medium text-slate-700">Po mesecima</span>
                <span class="text-xs text-slate-500">{{ $month->year }}</span>
            </div>
            <div class="mt-4 flex h-16 items-end gap-1.5">
                @foreach($monthNames as $i => $name)
                    @php $v = (float) ($perMonth[$i + 1] ?? 0); @endphp
                    <a href="{{ route('leave.index', ['m' => $month->year.'-'.str_pad($i + 1, 2, '0', STR_PAD_LEFT)]) }}" class="group flex h-full flex-1 flex-col justify-end" title="{{ $name }}: {{ LeaveDay::format($v) }}">
                        <span class="block w-full rounded-md {{ $v > 0 ? 'bg-teal-600' : 'bg-slate-100' }} transition group-hover:opacity-80 {{ ($i + 1) === $month->month ? 'ring-2 ring-teal-300' : '' }}" style="height: {{ $v > 0 ? max(12, $v / $maxMonth * 100) : 8 }}%"></span>
                    </a>
                @endforeach
            </div>
            <div class="mt-1.5 flex gap-1.5 text-[10px] text-slate-400">
                @foreach($monthNames as $name)<span class="flex-1 text-center">{{ mb_substr($name, 0, 1) }}</span>@endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">
        <section class="card rise p-5 sm:p-6">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold capitalize tracking-tight">{{ $monthLabel }}</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('leave.index', ['m' => $prev]) }}" class="icon-btn" aria-label="Prethodni mesec"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></a>
                    <a href="{{ route('leave.index') }}" class="btn !h-10">Danas</a>
                    <a href="{{ route('leave.index', ['m' => $next]) }}" class="icon-btn" aria-label="Sledeći mesec"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></a>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                @foreach($dayNames as $i => $name)
                    <div class="pb-1 text-center text-xs font-semibold uppercase tracking-wide {{ $i > 4 ? 'text-slate-400' : 'text-slate-500' }}">{{ $name }}</div>
                @endforeach

                @foreach($weeks as $week)
                    @foreach($week as $day)
                        @php
                            $key = $day->format('Y-m-d');
                            $leave = $days[$key] ?? null;
                            $inMonth = $day->month === $month->month;
                        @endphp
                        <button type="button" data-leave-cell
                            data-date="{{ $key }}"
                            data-pretty="{{ $day->copy()->locale('sr_Latn')->translatedFormat('l, j. F Y.') }}"
                            data-portion="{{ $leave?->portionKey() }}"
                            data-note="{{ $leave?->note }}"
                            data-delete="{{ $leave ? route('leave.destroy', $leave) : '' }}"
                            class="cal-cell {{ $inMonth ? '' : 'out' }} {{ $day->isWeekend() ? 'weekend' : '' }} {{ $key === $today ? 'today' : '' }} {{ $leave ? 'has' : '' }}">
                            @if($leave)<span class="cal-fill" style="--p: {{ (float) $leave->portion * 100 }}%"></span>@endif
                            <span class="relative flex items-start justify-between gap-1">
                                <span class="num">{{ $day->day }}</span>
                                @if($leave)<span class="cal-badge" title="{{ $leave->label() }}">{{ $leave->symbol() }}</span>@endif
                            </span>
                            @if($leave?->note)<span class="relative mt-auto hidden truncate text-[11px] text-slate-600 sm:block">{{ $leave->note }}</span>@endif
                        </button>
                    @endforeach
                @endforeach
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-slate-100 pt-4 text-xs text-slate-500">
                <span>Klikni na dan za unos ili izmenu.</span>
                <span class="flex items-center gap-1.5"><span class="cal-badge">1</span> ceo dan</span>
                <span class="flex items-center gap-1.5"><span class="cal-badge">½</span> pola dana</span>
                <span class="flex items-center gap-1.5"><span class="cal-badge">¼</span> četvrtina dana</span>
            </div>
        </section>

        <div class="space-y-5">
            <section class="card rise p-5 sm:p-6">
                <h2 class="text-lg font-semibold">Dodaj dane</h2>
                <p class="text-sm text-slate-500">Jedan dan ili ceo period sa istim delom dana.</p>
                <form method="POST" action="{{ route('leave.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <label class="block"><span class="mb-1.5 block text-sm font-medium">Od</span><input type="date" name="date" value="{{ old('date', $today) }}" class="field h-11 w-full px-3" required></label>
                        <label class="block"><span class="mb-1.5 block text-sm font-medium">Do <span class="font-normal text-slate-400">(opciono)</span></span><input type="date" name="date_to" value="{{ old('date_to') }}" class="field h-11 w-full px-3"></label>
                    </div>
                    <fieldset>
                        <legend class="mb-1.5 text-sm font-medium">Deo dana</legend>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(LeaveDay::PORTIONS as $value => $p)
                                <label class="portion-opt">
                                    <input type="radio" name="portion" value="{{ $value }}" class="peer sr-only" @checked(old('portion', '1.00') === (string) $value)>
                                    <span class="portion-card"><span class="text-xl font-semibold">{{ $p['symbol'] }}</span><span class="text-xs">{{ $p['label'] }}</span></span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <label class="block"><span class="mb-1.5 block text-sm font-medium">Napomena <span class="font-normal text-slate-400">(opciono)</span></span><input name="note" value="{{ old('note') }}" maxlength="255" placeholder="npr. VAB, odmor..." class="field h-11 w-full px-3"></label>
                    <x-form.toggle name="skip_weekends" label="Preskoči vikende" desc="Važi samo kad je izabran period." :checked="old('skip_weekends', true)" />
                    <button class="btn btn-primary w-full">Sačuvaj</button>
                </form>
            </section>

            <section class="card rise overflow-hidden">
                <div class="flex items-center justify-between p-5 sm:px-6">
                    <h2 class="text-lg font-semibold">Unosi {{ $month->year }}</h2>
                    <span class="chip bg-surface text-slate-700 ring-1 ring-slate-200">{{ $yearDays->count() }}</span>
                </div>
                <div class="max-h-96 divide-y divide-slate-100 overflow-auto border-t border-slate-100">
                    @forelse($yearDays as $d)
                        <div class="flex items-center gap-3 px-5 py-3 sm:px-6">
                            <span class="cal-badge shrink-0">{{ $d->symbol() }}</span>
                            <a href="{{ route('leave.index', ['m' => $d->date->format('Y-m')]) }}" class="min-w-0 flex-1">
                                <span class="block text-sm font-medium">{{ $d->date->copy()->locale('sr_Latn')->translatedFormat('D, j. M') }}</span>
                                <span class="block truncate text-xs text-slate-500">{{ $d->label() }}@if($d->note) · {{ $d->note }}@endif</span>
                            </a>
                            <form method="POST" action="{{ route('leave.destroy', $d) }}" onsubmit="return confirm('Obrisati ovaj dan?')">
                                @csrf
                                @method('DELETE')
                                <button class="icon-sm danger" title="Obriši"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg></button>
                            </form>
                        </div>
                    @empty
                        <p class="p-6 text-sm text-slate-500">Još nema unetih dana za {{ $month->year }}.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    {{-- Edit a single day (opened by clicking a calendar cell) --}}
    <dialog id="leaveDialog" class="card m-auto w-[min(26rem,calc(100vw-2rem))] p-0 text-slate-900 backdrop:bg-black/50 backdrop:backdrop-blur-sm">
        <form method="POST" action="{{ route('leave.store') }}" class="space-y-4 p-6">
            @csrf
            <input type="hidden" name="date" id="leaveDate">
            <div>
                <h3 class="text-lg font-semibold">Dan odmora</h3>
                <p id="leavePretty" class="text-sm text-slate-500 first-letter:uppercase"></p>
            </div>
            <div class="grid grid-cols-3 gap-2">
                @foreach(LeaveDay::PORTIONS as $value => $p)
                    <label class="portion-opt">
                        <input type="radio" name="portion" value="{{ $value }}" class="peer sr-only">
                        <span class="portion-card"><span class="text-xl font-semibold">{{ $p['symbol'] }}</span><span class="text-xs">{{ $p['label'] }}</span></span>
                    </label>
                @endforeach
            </div>
            <label class="block"><span class="mb-1.5 block text-sm font-medium">Napomena</span><input name="note" id="leaveNote" maxlength="255" class="field h-11 w-full px-3"></label>
            <div class="flex items-center gap-2 pt-1">
                <button class="btn btn-primary flex-1" autofocus>Sačuvaj</button>
                <button type="submit" form="leaveDeleteForm" id="leaveDeleteBtn" class="btn btn-danger" hidden>Obriši</button>
                <button type="button" class="btn" onclick="document.getElementById('leaveDialog').close()">Zatvori</button>
            </div>
        </form>
    </dialog>
    <form id="leaveDeleteForm" method="POST" hidden>@csrf @method('DELETE')</form>

    <script>
        (() => {
            const dlg = document.getElementById('leaveDialog');
            const del = document.getElementById('leaveDeleteForm');
            const delBtn = document.getElementById('leaveDeleteBtn');
            document.querySelectorAll('[data-leave-cell]').forEach((cell) => {
                cell.addEventListener('click', () => {
                    document.getElementById('leaveDate').value = cell.dataset.date;
                    document.getElementById('leavePretty').textContent = cell.dataset.pretty;
                    document.getElementById('leaveNote').value = cell.dataset.note || '';
                    const portion = cell.dataset.portion || '1.00';
                    dlg.querySelectorAll('input[name=portion]').forEach((r) => (r.checked = r.value === portion));
                    del.action = cell.dataset.delete || '';
                    delBtn.hidden = !cell.dataset.delete;
                    dlg.showModal();
                });
            });
            // clicking the dimmed backdrop closes the dialog
            dlg.addEventListener('click', (e) => { if (e.target === dlg) dlg.close(); });
        })();
    </script>
</x-layouts.app>
