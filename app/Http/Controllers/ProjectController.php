<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('client')->latest()->paginate(15);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create', [
            'project' => new Project(),
            'clients' => Client::orderBy('company_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $project = Project::create($this->validatedProject($request));

        return redirect()->route('projects.show', $project)->with('status', 'Projekat je kreiran.');
    }

    public function show(Request $request, Project $project)
    {
        $project->load(['client', 'workEntries' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'), 'payments' => fn ($query) => $query->latest('paid_on')]);

        if (! $request->user()->isSuperAdmin()) {
            $project->setRelation('workEntries', $project->workEntries->where('visible_to_client', true));
            $project->setRelation('payments', $project->payments->where('visible_to_client', true));
        }

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', [
            'project' => $project,
            'clients' => Client::orderBy('company_name')->get(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $project->update($this->validatedProject($request));

        return redirect()->route('projects.show', $project)->with('status', 'Projekat je sačuvan.');
    }

    public function destroy(Project $project)
    {
        $client = $project->client;
        $project->delete();

        return redirect()->route('clients.show', $client)->with('status', 'Projekat je obrisan.');
    }

    private function validatedProject(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,paused,completed'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'started_at' => ['nullable', 'date'],
            'finished_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ]);
    }
}
