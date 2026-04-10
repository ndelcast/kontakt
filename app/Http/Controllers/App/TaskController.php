<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Enums\TaskType;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $teamId = Auth::user()->current_team_id;

        $tasks = Task::where('team_id', $teamId)
            ->forUser(Auth::id())
            ->with(['opportunity', 'contact', 'company'])
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%");
            }))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => match ($status) {
                'pending' => $q->pending(),
                'completed' => $q->completed(),
                'overdue' => $q->overdue(),
                default => $q,
            })
            ->when($request->priority !== null && $request->priority !== '', fn ($q) => $q->where('priority', $request->priority))
            ->orderBy($request->sort ?? 'due_date', $request->direction ?? 'asc')
            ->paginate(15)
            ->withQueryString();

        $tasks->through(fn ($task) => $this->formatTask($task));

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'taskTypes' => collect(TaskType::cases())->map(fn ($t) => ['value' => $t->value, 'label' => $t->getLabel()]),
            'filters' => $request->only(['search', 'type', 'status', 'priority', 'sort', 'direction']),
        ]);
    }

    public function myDay(): Response
    {
        $teamId = Auth::user()->current_team_id;
        $userId = Auth::id();

        $overdue = Task::where('team_id', $teamId)->forUser($userId)->overdue()
            ->with(['opportunity', 'contact', 'company'])
            ->orderBy('due_date')->orderBy('due_time')->orderByDesc('priority')
            ->get()->map(fn ($t) => $this->formatTask($t));

        $today = Task::where('team_id', $teamId)->forUser($userId)->today()
            ->with(['opportunity', 'contact', 'company'])
            ->orderBy('due_time')->orderByDesc('priority')
            ->get()->map(fn ($t) => $this->formatTask($t));

        $upcoming = Task::where('team_id', $teamId)->forUser($userId)->upcoming()
            ->with(['opportunity', 'contact', 'company'])
            ->orderBy('due_date')->orderBy('due_time')->orderByDesc('priority')
            ->limit(10)
            ->get()->map(fn ($t) => $this->formatTask($t));

        return Inertia::render('Tasks/MyDay', [
            'overdue' => $overdue,
            'today' => $today,
            'upcoming' => $upcoming,
            'taskTypes' => collect(TaskType::cases())->map(fn ($t) => ['value' => $t->value, 'label' => $t->getLabel()]),
        ]);
    }

    public function create(): Response
    {
        $teamId = Auth::user()->current_team_id;

        return Inertia::render('Tasks/Create', [
            'taskTypes' => collect(TaskType::cases())->map(fn ($t) => ['value' => $t->value, 'label' => $t->getLabel()]),
            'opportunities' => Opportunity::where('team_id', $teamId)->orderBy('name')->get(['id', 'name']),
            'contacts' => Contact::where('team_id', $teamId)->orderBy('name')->get(['id', 'name']),
            'companies' => Company::where('team_id', $teamId)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', array_column(TaskType::cases(), 'value')),
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
            'priority' => 'required|in:0,1,2',
            'opportunity_id' => 'nullable|exists:opportunities,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $validated['team_id'] = Auth::user()->current_team_id;
        $validated['user_id'] = Auth::id();

        Task::create($validated);

        return redirect()->route('app.tasks.index')->with('success', __('Task created.'));
    }

    public function edit(Task $task): Response
    {
        $this->authorizeTeam($task);
        $teamId = Auth::user()->current_team_id;

        return Inertia::render('Tasks/Edit', [
            'task' => [
                'id' => $task->id,
                'type' => $task->type->value,
                'title' => $task->title,
                'description' => $task->description,
                'due_date' => $task->due_date?->format('Y-m-d'),
                'due_time' => $task->due_time?->format('H:i'),
                'priority' => $task->priority,
                'opportunity_id' => $task->opportunity_id,
                'contact_id' => $task->contact_id,
                'company_id' => $task->company_id,
                'completed_at' => $task->completed_at?->toIso8601String(),
                'outcome' => $task->outcome,
            ],
            'taskTypes' => collect(TaskType::cases())->map(fn ($t) => ['value' => $t->value, 'label' => $t->getLabel()]),
            'opportunities' => Opportunity::where('team_id', $teamId)->orderBy('name')->get(['id', 'name']),
            'contacts' => Contact::where('team_id', $teamId)->orderBy('name')->get(['id', 'name']),
            'companies' => Company::where('team_id', $teamId)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTeam($task);

        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', array_column(TaskType::cases(), 'value')),
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
            'priority' => 'required|in:0,1,2',
            'opportunity_id' => 'nullable|exists:opportunities,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'company_id' => 'nullable|exists:companies,id',
            'completed_at' => 'nullable|date',
            'outcome' => 'nullable|string',
        ]);

        $task->update($validated);

        return redirect()->route('app.tasks.index')->with('success', __('Task updated.'));
    }

    public function complete(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTeam($task);

        $task->update([
            'completed_at' => now(),
            'outcome' => $request->input('outcome'),
        ]);

        return back()->with('success', __('Task completed.'));
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorizeTeam($task);
        $task->delete();

        return redirect()->route('app.tasks.index')->with('success', __('Task deleted.'));
    }

    private function formatTask(Task $task): array
    {
        return [
            'id' => $task->id,
            'type' => $task->type->value,
            'type_label' => $task->type->getLabel(),
            'title' => $task->title,
            'description' => $task->description,
            'due_date' => $task->due_date?->toDateString(),
            'due_time' => $task->due_time?->format('H:i'),
            'priority' => $task->priority,
            'priority_label' => $task->priority_label,
            'is_overdue' => $task->isOverdue(),
            'is_today' => $task->isToday(),
            'completed_at' => $task->completed_at?->toIso8601String(),
            'outcome' => $task->outcome,
            'opportunity' => $task->opportunity ? ['id' => $task->opportunity->id, 'name' => $task->opportunity->name] : null,
            'contact' => $task->contact ? ['id' => $task->contact->id, 'name' => $task->contact->name] : null,
            'company' => $task->company ? ['id' => $task->company->id, 'name' => $task->company->name] : null,
        ];
    }

    private function authorizeTeam($model): void
    {
        abort_unless($model->team_id === Auth::user()->current_team_id, 403);
    }
}
