<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\PipelineStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityController extends Controller
{
    public function index(Request $request): Response
    {
        $teamId = Auth::user()->current_team_id;

        $opportunities = Opportunity::where('team_id', $teamId)
            ->with(['pipelineStage', 'company', 'contact'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->stage_id, fn ($q, $id) => $q->where('pipeline_stage_id', $id))
            ->when($request->company_id, fn ($q, $id) => $q->where('company_id', $id))
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate(15)
            ->withQueryString();

        $opportunities->through(fn ($o) => [
            'id' => $o->id,
            'name' => $o->name,
            'value' => $o->value,
            'started_at' => $o->started_at?->toDateString(),
            'expected_close_date' => $o->expected_close_date?->toDateString(),
            'won_at' => $o->won_at?->toDateString(),
            'lost_at' => $o->lost_at?->toDateString(),
            'stage' => $o->pipelineStage ? ['id' => $o->pipelineStage->id, 'name' => $o->pipelineStage->name, 'color' => $o->pipelineStage->color, 'is_won' => $o->pipelineStage->is_won, 'is_lost' => $o->pipelineStage->is_lost] : null,
            'company' => $o->company ? ['id' => $o->company->id, 'name' => $o->company->name] : null,
            'contact' => $o->contact ? ['id' => $o->contact->id, 'name' => $o->contact->name] : null,
        ]);

        $stages = PipelineStage::where('team_id', $teamId)->orderBy('position')->get(['id', 'name']);
        $companies = Company::where('team_id', $teamId)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Opportunities/Index', [
            'opportunities' => $opportunities,
            'stages' => $stages,
            'companies' => $companies,
            'filters' => $request->only(['search', 'stage_id', 'company_id', 'sort', 'direction']),
        ]);
    }

    public function create(): Response
    {
        $teamId = Auth::user()->current_team_id;

        return Inertia::render('Opportunities/Create', [
            'stages' => PipelineStage::where('team_id', $teamId)->orderBy('position')->get(['id', 'name']),
            'companies' => Company::where('team_id', $teamId)->orderBy('name')->get(['id', 'name']),
            'contacts' => Contact::where('team_id', $teamId)->orderBy('name')->get(['id', 'name', 'company_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
            'company_id' => 'nullable|exists:companies,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'value' => 'nullable|numeric|min:0',
            'started_at' => 'nullable|date',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['team_id'] = Auth::user()->current_team_id;
        $validated['value'] = $validated['value'] ?? 0;
        $validated['started_at'] = $validated['started_at'] ?? now();

        Opportunity::create($validated);

        return redirect()->route('app.opportunities.index')->with('success', __('Opportunity created.'));
    }

    public function edit(Opportunity $opportunity): Response
    {
        $this->authorizeTeam($opportunity);
        $teamId = Auth::user()->current_team_id;

        return Inertia::render('Opportunities/Edit', [
            'opportunity' => [
                'id' => $opportunity->id,
                'name' => $opportunity->name,
                'pipeline_stage_id' => $opportunity->pipeline_stage_id,
                'company_id' => $opportunity->company_id,
                'contact_id' => $opportunity->contact_id,
                'value' => $opportunity->value,
                'started_at' => $opportunity->started_at?->format('Y-m-d'),
                'expected_close_date' => $opportunity->expected_close_date?->format('Y-m-d'),
                'won_at' => $opportunity->won_at?->format('Y-m-d'),
                'notes' => $opportunity->notes,
            ],
            'stages' => PipelineStage::where('team_id', $teamId)->orderBy('position')->get(['id', 'name']),
            'companies' => Company::where('team_id', $teamId)->orderBy('name')->get(['id', 'name']),
            'contacts' => Contact::where('team_id', $teamId)->orderBy('name')->get(['id', 'name', 'company_id']),
        ]);
    }

    public function update(Request $request, Opportunity $opportunity): RedirectResponse
    {
        $this->authorizeTeam($opportunity);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
            'company_id' => 'nullable|exists:companies,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'value' => 'nullable|numeric|min:0',
            'started_at' => 'nullable|date',
            'expected_close_date' => 'nullable|date',
            'won_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $opportunity->update($validated);

        return redirect()->route('app.opportunities.index')->with('success', __('Opportunity updated.'));
    }

    public function destroy(Opportunity $opportunity): RedirectResponse
    {
        $this->authorizeTeam($opportunity);
        $opportunity->delete();

        return redirect()->route('app.opportunities.index')->with('success', __('Opportunity deleted.'));
    }

    public function kanban(): Response
    {
        $teamId = Auth::user()->current_team_id;

        $stages = PipelineStage::where('team_id', $teamId)
            ->orderBy('position')
            ->get()
            ->map(fn ($stage) => [
                'id' => $stage->id,
                'name' => $stage->name,
                'color' => $stage->color,
                'is_won' => $stage->is_won,
                'is_lost' => $stage->is_lost,
            ]);

        $opportunities = Opportunity::where('team_id', $teamId)
            ->with(['company', 'contact', 'pipelineStage'])
            ->whereNull('won_at')
            ->whereNull('lost_at')
            ->orderBy('position')
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'name' => $o->name,
                'value' => $o->value,
                'pipeline_stage_id' => $o->pipeline_stage_id,
                'position' => $o->position,
                'expected_close_date' => $o->expected_close_date?->toDateString(),
                'started_at' => $o->started_at?->toDateString(),
                'days_open' => $o->started_at ? (int) $o->started_at->diffInDays(now()) : 0,
                'company' => $o->company ? ['id' => $o->company->id, 'name' => $o->company->name] : null,
                'contact' => $o->contact ? ['id' => $o->contact->id, 'name' => $o->contact->name] : null,
            ]);

        // Also include won/lost for their columns
        $closedOpportunities = Opportunity::where('team_id', $teamId)
            ->with(['company', 'contact', 'pipelineStage'])
            ->where(fn ($q) => $q->whereNotNull('won_at')->orWhereNotNull('lost_at'))
            ->orderByDesc('won_at')
            ->orderByDesc('lost_at')
            ->limit(20)
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'name' => $o->name,
                'value' => $o->value,
                'pipeline_stage_id' => $o->pipeline_stage_id,
                'position' => $o->position,
                'expected_close_date' => $o->expected_close_date?->toDateString(),
                'started_at' => $o->started_at?->toDateString(),
                'won_at' => $o->won_at?->toDateString(),
                'lost_at' => $o->lost_at?->toDateString(),
                'days_open' => $o->started_at ? (int) $o->started_at->diffInDays($o->won_at ?? $o->lost_at ?? now()) : 0,
                'company' => $o->company ? ['id' => $o->company->id, 'name' => $o->company->name] : null,
                'contact' => $o->contact ? ['id' => $o->contact->id, 'name' => $o->contact->name] : null,
            ]);

        $allOpportunities = $opportunities->merge($closedOpportunities);

        $companies = Company::where('team_id', $teamId)->orderBy('name')->get(['id', 'name']);
        $contacts = Contact::where('team_id', $teamId)->orderBy('name')->get(['id', 'name', 'company_id']);
        $stagesList = PipelineStage::where('team_id', $teamId)->orderBy('position')->get(['id', 'name']);

        return Inertia::render('Opportunities/Kanban', [
            'stages' => $stages,
            'opportunities' => $allOpportunities,
            'companies' => $companies,
            'contacts' => $contacts,
            'stagesList' => $stagesList,
        ]);
    }

    public function kanbanMove(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'opportunity_id' => 'required|exists:opportunities,id',
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
            'position' => 'required|integer|min:0',
        ]);

        $opportunity = Opportunity::findOrFail($validated['opportunity_id']);
        $this->authorizeTeam($opportunity);

        $stage = PipelineStage::findOrFail($validated['pipeline_stage_id']);

        $opportunity->update([
            'pipeline_stage_id' => $stage->id,
            'position' => $validated['position'],
            'won_at' => $stage->is_won ? now() : $opportunity->won_at,
            'lost_at' => $stage->is_lost ? now() : $opportunity->lost_at,
        ]);

        return response()->json(['success' => true]);
    }

    private function authorizeTeam($model): void
    {
        abort_unless($model->team_id === Auth::user()->current_team_id, 403);
    }
}
