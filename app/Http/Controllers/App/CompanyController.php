<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(Request $request): Response
    {
        $teamId = Auth::user()->current_team_id;

        $companies = Company::where('team_id', $teamId)
            ->withCount(['contacts', 'opportunities'])
            ->when($request->search, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('industry', 'like', "%{$search}%");
            }))
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate(15)
            ->withQueryString();

        $companies->through(fn ($company) => [
            'id' => $company->id,
            'name' => $company->name,
            'industry' => $company->industry,
            'website' => $company->website,
            'phone' => $company->phone,
            'contacts_count' => $company->contacts_count,
            'opportunities_count' => $company->opportunities_count,
            'created_at' => $company->created_at->diffForHumans(),
        ]);

        return Inertia::render('Companies/Index', [
            'companies' => $companies,
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Companies/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['team_id'] = Auth::user()->current_team_id;

        Company::create($validated);

        return redirect()->route('app.companies.index')->with('success', __('Company created.'));
    }

    public function show(Company $company): Response
    {
        $this->authorizeTeam($company);

        $company->load(['contacts', 'opportunities.pipelineStage']);

        return Inertia::render('Companies/Show', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'industry' => $company->industry,
                'website' => $company->website,
                'phone' => $company->phone,
                'address' => $company->address,
                'notes' => $company->notes,
                'contacts' => $company->contacts->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'email' => $c->email,
                    'phone' => $c->phone,
                    'position' => $c->position,
                ]),
                'opportunities' => $company->opportunities->map(fn ($o) => [
                    'id' => $o->id,
                    'name' => $o->name,
                    'value' => $o->value,
                    'stage' => $o->pipelineStage?->name,
                    'stage_color' => $o->pipelineStage?->color,
                    'expected_close_date' => $o->expected_close_date?->toDateString(),
                ]),
            ],
        ]);
    }

    public function edit(Company $company): Response
    {
        $this->authorizeTeam($company);

        return Inertia::render('Companies/Edit', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'industry' => $company->industry,
                'website' => $company->website,
                'phone' => $company->phone,
                'address' => $company->address,
                'notes' => $company->notes,
            ],
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $this->authorizeTeam($company);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $company->update($validated);

        return redirect()->route('app.companies.index')->with('success', __('Company updated.'));
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->authorizeTeam($company);
        $company->delete();

        return redirect()->route('app.companies.index')->with('success', __('Company deleted.'));
    }

    private function authorizeTeam($model): void
    {
        abort_unless($model->team_id === Auth::user()->current_team_id, 403);
    }
}
