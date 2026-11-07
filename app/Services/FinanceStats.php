<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\WorkEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinanceStats
{
    private const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Avg', 'Sep', 'Okt', 'Nov', 'Dec'];

    /**
     * Build chart series (last 12 months + per year) for work, paid and expenses.
     *
     * @param  Collection<int, WorkEntry>  $work
     * @param  Collection<int, Payment>  $payments
     */
    public static function series(Collection $work, Collection $payments): array
    {
        $rows = [];
        $add = function (?Carbon $date, string $key, float $amount) use (&$rows) {
            if (! $date) {
                return;
            }
            $m = $date->format('Y-m');
            $y = $date->format('Y');
            $rows['m'][$m][$key] = ($rows['m'][$m][$key] ?? 0) + $amount;
            $rows['y'][$y][$key] = ($rows['y'][$y][$key] ?? 0) + $amount;
        };

        foreach ($work as $entry) {
            $add($entry->worked_on ?? $entry->created_at, 'work', (float) $entry->amount);
        }
        foreach ($payments as $payment) {
            if ($payment->type === 'expense') {
                $add($payment->paid_on, 'expenses', (float) $payment->amount);
            } elseif ($payment->status === 'paid') {
                $add($payment->paid_on, 'paid', (float) $payment->amount);
            }
        }

        $point = fn (array $r, string $label) => [
            'label' => $label,
            'work' => round($r['work'] ?? 0, 2),
            'paid' => round($r['paid'] ?? 0, 2),
            'expenses' => round($r['expenses'] ?? 0, 2),
        ];

        $months = [];
        $cursor = now()->startOfMonth()->subMonths(11);
        for ($i = 0; $i < 12; $i++) {
            $months[] = $point($rows['m'][$cursor->format('Y-m')] ?? [], self::MONTHS[$cursor->month - 1]);
            $cursor = $cursor->copy()->addMonth();
        }

        $years = collect($rows['y'] ?? [])->sortKeys()->map(fn ($r, $y) => $point($r, (string) $y))->values()->all();
        if (count($years) < 2) {
            $years = [$point($rows['y'][(string) (now()->year - 1)] ?? [], (string) (now()->year - 1)), $point($rows['y'][(string) now()->year] ?? [], (string) now()->year)];
        }

        return ['months' => $months, 'years' => $years];
    }

    /** Percent change of last vs previous month for a key, null when no baseline. */
    public static function trend(array $months, string $key): ?float
    {
        $cur = $months[count($months) - 1][$key] ?? 0;
        $prev = $months[count($months) - 2][$key] ?? 0;

        return $prev > 0 ? round(($cur - $prev) / $prev * 100, 1) : null;
    }
}
