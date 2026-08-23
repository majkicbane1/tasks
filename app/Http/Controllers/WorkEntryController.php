<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WorkEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkEntryController extends Controller
{
    public function index()
    {
        return redirect()->route('dashboard');
    }

    public function create(Request $request)
    {
        $project = Project::with('client')->findOrFail($request->integer('project_id'));

        return view('work-entries.create', ['entry' => new WorkEntry(), 'project' => $project]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedEntry($request);
        $project = Project::with('client')->findOrFail($data['project_id']);
        $data = $this->withCalculatedAmount($data, $project);

        $entry = WorkEntry::create($data);

        return redirect()->route('projects.show', $entry->project)->with('status', 'Stavka je dodata.');
    }

    public function show(string $id)
    {
        return redirect()->route('dashboard');
    }

    public function edit(WorkEntry $workEntry)
    {
        return view('work-entries.edit', ['entry' => $workEntry->load('project.client'), 'project' => $workEntry->project]);
    }

    public function update(Request $request, WorkEntry $workEntry)
    {
        $data = $this->validatedEntry($request);
        $project = Project::with('client')->findOrFail($data['project_id']);
        $workEntry->update($this->withCalculatedAmount($data, $project));

        return redirect()->route('projects.show', $workEntry->project)->with('status', 'Stavka je sačuvana.');
    }

    public function destroy(WorkEntry $workEntry)
    {
        $project = $workEntry->project;
        $workEntry->delete();

        return redirect()->route('projects.show', $project)->with('status', 'Stavka je obrisana.');
    }

    private function validatedEntry(Request $request): array
    {
        return $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'worked_on' => ['nullable', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'hours' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'fixed_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['billable', 'included', 'draft'])],
            'visible_to_client' => ['nullable', 'boolean'],
            'internal_note' => ['nullable', 'string'],
        ]) + ['visible_to_client' => false];
    }

    private function withCalculatedAmount(array $data, Project $project): array
    {
        $rate = (float) ($data['hourly_rate'] ?: $project->hourly_rate ?: $project->client->default_hourly_rate);
        $fixed = $data['fixed_amount'] !== null && $data['fixed_amount'] !== '' ? (float) $data['fixed_amount'] : null;
        $hours = $data['hours'] !== null && $data['hours'] !== '' ? (float) $data['hours'] : null;

        $data['hourly_rate'] = $rate;
        $data['amount'] = $data['status'] === 'billable' ? ($fixed ?? (($hours ?? 0) * $rate)) : 0;

        return $data;
    }
}
