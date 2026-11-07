<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\WorkEntry;
use App\Services\ClientLists;
use App\Services\FinanceStats;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            $clients = Client::withCount('projects')->latest()->get();
            $projects = Project::with('client')->latest()->limit(8)->get();
            $workTotal = (float) WorkEntry::sum('amount');
            $paymentsTotal = (float) Payment::where('type', 'payment')->where('status', 'paid')->sum('amount');
            $expensesTotal = (float) Payment::where('type', 'expense')->sum('amount');

            $series = FinanceStats::series(WorkEntry::get(['worked_on', 'created_at', 'amount']), Payment::get(['paid_on', 'amount', 'type', 'status']));

            return view('dashboard', [
                'series' => $series,
                'trends' => [
                    'work' => FinanceStats::trend($series['months'], 'work'),
                    'paid' => FinanceStats::trend($series['months'], 'paid'),
                    'expenses' => FinanceStats::trend($series['months'], 'expenses'),
                ],
                'invoiced' => (float) Payment::where('type', 'payment')->where('status', 'pending_invoice')->sum('amount'),
                'unbilled' => (float) WorkEntry::whereNull('payment_id')->sum('amount'),
                'clients' => $clients,
                'projects' => $projects,
                'stats' => [
                    'work' => $workTotal,
                    'paid' => $paymentsTotal,
                    'expenses' => $expensesTotal,
                    'balance' => $workTotal + $expensesTotal - $paymentsTotal,
                    'clients' => $clients->count(),
                    'projects' => Project::count(),
                ],
            ]);
        }

        $client = $user->client?->load(['projects.workEntries', 'payments.project']);
        abort_unless($client, 403);

        return view('client-dashboard', ['client' => $client] + ClientLists::firstPages($client, false, route('client.more')));
    }

    /** AJAX "load more" for the logged in client's own lists. */
    public function clientMore(Request $request)
    {
        $client = $request->user()->client;
        abort_unless($client && ! $request->user()->isSuperAdmin(), 403);

        return ClientLists::more($request, $client, false);
    }

    public function clientProjects(Request $request)
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('projects.index');
        }

        $client = $user->client;
        abort_unless($client, 403);

        return view('client-projects', [
            'client' => $client,
            'projects' => $client->projects()->latest()->get(),
        ]);
    }
}
