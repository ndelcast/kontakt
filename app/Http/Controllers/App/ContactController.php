<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(Request $request): Response
    {
        $teamId = Auth::user()->current_team_id;

        $contacts = Contact::where('team_id', $teamId)
            ->with('company')
            ->when($request->search, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->when($request->company_id, fn ($q, $id) => $q->where('company_id', $id))
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate(15)
            ->withQueryString();

        $contacts->through(fn ($contact) => [
            'id' => $contact->id,
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'position' => $contact->position,
            'company' => $contact->company ? ['id' => $contact->company->id, 'name' => $contact->company->name] : null,
            'created_at' => $contact->created_at->toDateTimeString(),
        ]);

        $companies = Company::where('team_id', $teamId)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'companies' => $companies,
            'filters' => $request->only(['search', 'company_id', 'sort', 'direction']),
        ]);
    }

    public function create(): Response
    {
        $teamId = Auth::user()->current_team_id;
        $companies = Company::where('team_id', $teamId)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Contacts/Create', [
            'companies' => $companies,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'notes' => 'nullable|string',
        ]);

        $validated['team_id'] = Auth::user()->current_team_id;

        Contact::create($validated);

        return redirect()->route('app.contacts.index')->with('success', __('Contact created.'));
    }

    public function edit(Contact $contact): Response
    {
        $this->authorizeTeam($contact);
        $teamId = Auth::user()->current_team_id;
        $companies = Company::where('team_id', $teamId)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Contacts/Edit', [
            'contact' => [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'position' => $contact->position,
                'company_id' => $contact->company_id,
                'notes' => $contact->notes,
            ],
            'companies' => $companies,
        ]);
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $this->authorizeTeam($contact);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'notes' => 'nullable|string',
        ]);

        $contact->update($validated);

        return redirect()->route('app.contacts.index')->with('success', __('Contact updated.'));
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $this->authorizeTeam($contact);
        $contact->delete();

        return redirect()->route('app.contacts.index')->with('success', __('Contact deleted.'));
    }

    private function authorizeTeam($model): void
    {
        abort_unless($model->team_id === Auth::user()->current_team_id, 403);
    }
}
