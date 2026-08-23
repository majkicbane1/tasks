<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\WorkEntry;
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
            $paymentsTotal = (float) Payment::where('type', 'payment')->sum('amount');
            $expensesTotal = (float) Payment::where('type', 'expense')->sum('amount');

            return view('dashboard', [
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

        return view('client-dashboard', [
            'client' => $client,
            'workEntries' => WorkEntry::whereHas('project', fn ($query) => $query->where('client_id', $client->id))
                ->with('project')
                ->where('visible_to_client', true)
                ->latest('worked_on')
                ->limit(20)
                ->get(),
            'payments' => $client->payments()->with('project')->where('visible_to_client', true)->latest('paid_on')->get(),
        ]);
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
