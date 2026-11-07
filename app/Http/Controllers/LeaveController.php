<?php

namespace App\Http\Controllers;

use App\Models\LeaveDay;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $monthParam = (string) $request->query('m');
        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthParam)
            ? Carbon::createFromFormat('Y-m-d', $monthParam.'-01')->startOfDay()
            : now()->startOfMonth();

        $gridStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $days = LeaveDay::whereBetween('date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->get()
            ->keyBy(fn (LeaveDay $day) => $day->date->format('Y-m-d'));

        $yearDays = LeaveDay::whereYear('date', $month->year)->orderByDesc('date')->get();
        $perMonth = $yearDays->groupBy(fn (LeaveDay $day) => $day->date->month)
            ->map(fn ($group) => (float) $group->sum('portion'));

        return view('leave.index', [
            'month' => $month,
            'weeks' => collect(CarbonPeriod::create($gridStart, $gridEnd))->chunk(7),
            'days' => $days,
            'yearDays' => $yearDays,
            'yearTotal' => (float) $yearDays->sum('portion'),
            'monthTotal' => (float) $yearDays->filter(fn (LeaveDay $day) => $day->date->month === $month->month)->sum('portion'),
            'perMonth' => $perMonth,
            'prev' => $month->copy()->subMonth()->format('Y-m'),
            'next' => $month->copy()->addMonth()->format('Y-m'),
        ]);
    }

    /** Adds (or updates) one day, or a whole range of days with the same share. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date'],
            'portion' => ['required', Rule::in(array_keys(LeaveDay::PORTIONS))],
            'note' => ['nullable', 'string', 'max:255'],
            'skip_weekends' => ['nullable', 'boolean'],
        ]);

        $from = Carbon::parse($data['date'])->startOfDay();
        $to = isset($data['date_to']) ? Carbon::parse($data['date_to'])->startOfDay() : $from->copy();
        abort_if($from->diffInDays($to) > 366, 422, 'Opseg može da bude najviše godinu dana.');

        $skipWeekends = $request->boolean('skip_weekends') && $from->ne($to);
        $count = 0;

        foreach (CarbonPeriod::create($from, $to) as $day) {
            if ($skipWeekends && $day->isWeekend()) {
                continue;
            }
            LeaveDay::updateOrCreate(
                ['date' => $day->toDateString()],
                ['portion' => $data['portion'], 'note' => $data['note'] ?? null],
            );
            $count++;
        }

        return redirect()
            ->route('leave.index', ['m' => $from->format('Y-m')])
            ->with('status', $count === 1 ? 'Dan je sačuvan.' : "Sačuvano dana: {$count}.");
    }

    public function destroy(LeaveDay $leaveDay)
    {
        $month = $leaveDay->date->format('Y-m');
        $leaveDay->delete();

        return redirect()->route('leave.index', ['m' => $month])->with('status', 'Dan je obrisan.');
    }
}
