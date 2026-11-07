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
        $data['sort_order'] = ((int) WorkEntry::max('sort_order')) + 10;

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

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'entries' => ['required', 'array'],
            'entries.*' => ['integer', 'exists:work_entries,id'],
        ]);

        $ids = $data['entries'];

        // Only part of the list may be loaded (load more), so reuse the sort values these
        // entries already hold and hand them out in the new order instead of renumbering.
        $slots = WorkEntry::whereIn('id', $ids)->pluck('sort_order')->sortDesc()->values();
        $valid = $slots->count() === count($ids) && $slots->min() > 0 && $slots->unique()->count() === $slots->count();

        foreach ($ids as $index => $entryId) {
            WorkEntry::whereKey($entryId)->update([
                'sort_order' => $valid ? $slots[$index] : (count($ids) - $index) * 10,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer', 'exists:work_entries,id'],
            'worked_on' => ['nullable', 'date', 'required_without:state'],
            'state' => ['nullable', Rule::in(['open', 'invoiced', 'paid']), 'required_without:worked_on'],
        ]);

        $query = WorkEntry::whereIn('id', $data['ids']);
        $response = ['updated' => (clone $query)->count()];

        if (! empty($data['worked_on'])) {
            (clone $query)->update(['worked_on' => $data['worked_on']]);
            $response['date'] = \Illuminate\Support\Carbon::parse($data['worked_on'])->format('d.m.Y.');
        }

        // Only the marker on the entry changes; invoices/payments (and so the debt) are not touched.
        if (! empty($data['state'])) {
            match ($data['state']) {
                'open' => (clone $query)->update(['invoiced_at' => null, 'paid_at' => null]),
                'invoiced' => $this->markInvoiced($query),
                'paid' => $this->markPaid($query),
            };
            $response['state'] = $data['state'];
        }

        return response()->json($response);
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer'],
        ]);

        return response()->json(['deleted' => WorkEntry::whereIn('id', $data['ids'])->delete()]);
    }

    private function markInvoiced($query): void
    {
        (clone $query)->whereNull('invoiced_at')->update(['invoiced_at' => now()]);
        (clone $query)->update(['paid_at' => null]);
    }

    private function markPaid($query): void
    {
        (clone $query)->whereNull('invoiced_at')->update(['invoiced_at' => now()]);
        (clone $query)->whereNull('paid_at')->update(['paid_at' => now()]);
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
