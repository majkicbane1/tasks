<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\WorkEntry;

class SharedClientController extends Controller
{
    public function dashboard(string $token)
    {
        $client = $this->client($token);

        return view('shared.dashboard', [
            'client' => $client,
            'workEntries' => $this->workEntries($client)->limit(20)->get(),
            'payments' => $client->payments()->with('project')->where('visible_to_client', true)->latest('paid_on')->get(),
            'shareToken' => $token,
        ]);
    }

    public function projects(string $token)
    {
        $client = $this->client($token);

        return view('shared.projects', [
            'client' => $client,
            'projects' => $client->projects()->latest()->get(),
            'shareToken' => $token,
        ]);
    }

    public function project(string $token, Project $project)
    {
        $client = $this->client($token);
        abort_unless((int) $project->client_id === (int) $client->id, 404);

        $project->load([
            'client',
            'workEntries' => fn ($query) => $query->where('visible_to_client', true)->orderBy('sort_order')->orderBy('id'),
            'payments' => fn ($query) => $query->where('visible_to_client', true)->latest('paid_on'),
        ]);

        return view('shared.project', compact('client', 'project', 'shareToken'));
    }

    private function client(string $token): Client
    {
        return Client::where('share_token', $token)->firstOrFail();
    }

    private function workEntries(Client $client)
    {
        return WorkEntry::whereHas('project', fn ($query) => $query->where('client_id', $client->id))
            ->with('project')
            ->where('visible_to_client', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
