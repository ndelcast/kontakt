<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\PipelineStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PipelineStageController extends Controller
{
    public function index(): Response
    {
        $teamId = Auth::user()->current_team_id;

        $stages = PipelineStage::where('team_id', $teamId)
            ->withCount('opportunities')
            ->orderBy('position')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'slug' => $s->slug,
                'color' => $s->color,
                'probability' => $s->probability,
                'position' => $s->position,
                'is_won' => $s->is_won,
                'is_lost' => $s->is_lost,
                'opportunities_count' => $s->opportunities_count,
            ]);

        return Inertia::render('Pipeline/Index', [
            'stages' => $stages,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Pipeline/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pipeline_stages,slug',
            'color' => 'nullable|string',
            'probability' => 'required|integer|min:0|max:100',
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
        ]);

        $validated['team_id'] = Auth::user()->current_team_id;

        PipelineStage::create($validated);

        return redirect()->route('app.pipeline.index')->with('success', __('Stage created.'));
    }

    public function edit(PipelineStage $stage): Response
    {
        $this->authorizeTeam($stage);

        return Inertia::render('Pipeline/Edit', [
            'stage' => [
                'id' => $stage->id,
                'name' => $stage->name,
                'slug' => $stage->slug,
                'color' => $stage->color,
                'probability' => $stage->probability,
                'is_won' => $stage->is_won,
                'is_lost' => $stage->is_lost,
            ],
        ]);
    }

    public function update(Request $request, PipelineStage $stage): RedirectResponse
    {
        $this->authorizeTeam($stage);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pipeline_stages,slug,' . $stage->id,
            'color' => 'nullable|string',
            'probability' => 'required|integer|min:0|max:100',
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
        ]);

        $stage->update($validated);

        return redirect()->route('app.pipeline.index')->with('success', __('Stage updated.'));
    }

    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:pipeline_stages,id',
        ]);

        foreach ($validated['ids'] as $position => $id) {
            PipelineStage::where('id', $id)
                ->where('team_id', Auth::user()->current_team_id)
                ->update(['position' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(PipelineStage $stage): RedirectResponse
    {
        $this->authorizeTeam($stage);
        $stage->delete();

        return redirect()->route('app.pipeline.index')->with('success', __('Stage deleted.'));
    }

    private function authorizeTeam($model): void
    {
        abort_unless($model->team_id === Auth::user()->current_team_id, 403);
    }
}
