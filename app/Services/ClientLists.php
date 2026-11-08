<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Project;
use App\Models\WorkEntry;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Work and payment lists of a client, paged for the AJAX "load more" buttons.
 * $admin = true is the super admin view; false is a client facing view (own login or share link)
 * that only sees entries marked as visible and gets read-only rows.
 */
class ClientLists
{
    public const PER_PAGE = 15;

    /** $period is a chart bucket: "2026" (year) or "2026-03" (month); anything else is ignored. */
    public static function work(Client $client, bool $admin, ?string $period = null, ?string $sort = null, string $dir = 'desc')
    {
        $from = $to = null;
        if ($period && preg_match('/^\d{4}(-(0[1-9]|1[0-2]))?$/', $period)) {
            $from = strlen($period) === 4 ? Carbon::create((int) $period, 1, 1) : Carbon::createFromFormat('!Y-m', $period);
            $to = strlen($period) === 4 ? $from->copy()->addYear() : $from->copy()->addMonth();
        }

        return WorkEntry::whereHas('project', fn ($query) => $query->where('client_id', $client->id))
            ->with('project')
            ->when($from, fn ($query) => $query->whereRaw('COALESCE(worked_on, created_at) >= ? and COALESCE(worked_on, created_at) < ?', [$from, $to]))
            ->when(! $admin, fn ($query) => $query->where('visible_to_client', true))
            ->when($sort, fn ($query) => self::applySort($query, $sort, $dir))
            ->orderByDesc('sort_order')
            ->orderByDesc('id');
    }

    /** Column sort chosen by clicking a table header; unknown keys are ignored (default order stays). */
    private static function applySort($query, string $sort, string $dir): void
    {
        $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';
        match ($sort) {
            'date' => $query->orderByRaw("COALESCE(worked_on, created_at) $dir"),
            'project' => $query->orderBy(Project::select('name')->whereColumn('projects.id', 'work_entries.project_id'), $dir),
            'title' => $query->orderBy('title', $dir),
            'hours' => $query->orderBy('hours', $dir),
            'amount' => $query->orderBy('amount', $dir),
            'status' => $query->orderByRaw("CASE WHEN paid_at IS NOT NULL THEN 2 WHEN invoiced_at IS NOT NULL THEN 1 ELSE 0 END $dir"),
            default => null,
        };
    }

    public static function payments(Client $client, bool $admin)
    {
        return $client->payments()
            ->with('project')
            ->when(! $admin, fn ($query) => $query->where('visible_to_client', true))
            ->latest('paid_on')
            ->orderByDesc('id');
    }

    /** First page of both lists plus the flags the views need. */
    public static function firstPages(Client $client, bool $admin, string $moreUrl): array
    {
        $work = self::work($client, $admin)->paginate(self::PER_PAGE);
        $payments = self::payments($client, $admin)->paginate(self::PER_PAGE);

        return [
            'workEntries' => $work->getCollection(),
            'workHasMore' => $work->hasMorePages(),            'payments' => $payments->getCollection(),
            'paymentsHasMore' => $payments->hasMorePages(),
            'moreUrl' => $moreUrl,
        ];
    }

    /** JSON with rendered rows for one more page. */
    public static function more(Request $request, Client $client, bool $admin): JsonResponse
    {
        $type = $request->query('type');
        abort_unless(in_array($type, ['work', 'payments'], true), 404);
        $page = max(1, $request->integer('page', 1));

        if ($type === 'work') {
            $paginator = self::work($client, $admin, $request->query('period'), $request->query('sort'), (string) $request->query('dir', 'desc'))->paginate(self::PER_PAGE, ['*'], 'page', $page);
            $data = [
                'canManage' => $admin,
                'showProject' => true,
                'invoiceClient' => $admin ? $client : null,
                'mutePaid' => ! $admin,
                'currency' => $client->currency,
            ];
            $items = $paginator->getCollection();

            return response()->json([
                'rows' => $items->map(fn ($entry) => view('projects.partials.work-row', $data + ['entry' => $entry])->render())->implode(''),
                'cards' => $items->map(fn ($entry) => view('projects.partials.work-card', $data + ['entry' => $entry])->render())->implode(''),
                'has_more' => $paginator->hasMorePages(),
            ]);
        }

        $paginator = self::payments($client, $admin)->paginate(self::PER_PAGE, ['*'], 'page', $page);
        $data = ['currency' => $client->currency, 'readonly' => ! $admin, 'allowMarkPaid' => $admin];

        return response()->json([
            'rows' => $paginator->getCollection()->map(fn ($payment) => view('projects.partials.payment-row', $data + ['payment' => $payment])->render())->implode(''),
            'has_more' => $paginator->hasMorePages(),
        ]);
    }
}
