<?php

namespace App\Filament\Pages;

use App\Enums\TaskType;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class MyDay extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sun';

    protected static string $view = 'filament.pages.my-day';

    protected static ?int $navigationSort = -1;

    public function getTitle(): string
    {
        return __('My Day');
    }

    public static function getNavigationLabel(): string
    {
        return __('My Day');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Task::forUser(Auth::id())->pending()->where(function ($q) {
            $q->whereNull('due_date')
                ->orWhere('due_date', '<=', now()->toDateString());
        })->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $overdueCount = Task::forUser(Auth::id())->overdue()->count();
        return $overdueCount > 0 ? 'danger' : 'primary';
    }

    public function getOverdueTasks(): Collection
    {
        return Task::forUser(Auth::id())
            ->overdue()
            ->with(['opportunity', 'contact', 'company'])
            ->orderBy('due_date')
            ->orderBy('due_time')
            ->orderByDesc('priority')
            ->get();
    }

    public function getTodayTasks(): Collection
    {
        return Task::forUser(Auth::id())
            ->today()
            ->with(['opportunity', 'contact', 'company'])
            ->orderBy('due_time')
            ->orderByDesc('priority')
            ->get();
    }

    public function getUpcomingTasks(): Collection
    {
        return Task::forUser(Auth::id())
            ->upcoming()
            ->with(['opportunity', 'contact', 'company'])
            ->orderBy('due_date')
            ->orderBy('due_time')
            ->orderByDesc('priority')
            ->limit(10)
            ->get();
    }

    public function completeTask(int $taskId, ?string $outcome = null): void
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);
        $task->update([
            'completed_at' => now(),
            'outcome' => $outcome,
        ]);

        Notification::make()
            ->success()
            ->title(__('Task completed'))
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('all-tasks')
                ->label(__('All Tasks'))
                ->icon('heroicon-o-list-bullet')
                ->url(fn () => route('filament.admin.resources.tasks.index'))
                ->color('gray'),
            CreateAction::make('create-task')
                ->label(__('New Task'))
                ->model(Task::class)
                ->form([
                    Select::make('type')
                        ->label(__('Type'))
                        ->options(TaskType::class)
                        ->required()
                        ->default(TaskType::Call),
                    TextInput::make('title')
                        ->label(__('Title'))
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label(__('Description')),
                    DatePicker::make('due_date')
                        ->label(__('Due date'))
                        ->displayFormat('d/m/Y')
                        ->default(now()),
                    TimePicker::make('due_time')
                        ->label(__('Due time'))
                        ->seconds(false),
                    Select::make('priority')
                        ->label(__('Priority'))
                        ->options([
                            0 => __('Normal'),
                            1 => __('High'),
                            2 => __('Urgent'),
                        ])
                        ->default(0),
                    Select::make('opportunity_id')
                        ->label(__('Opportunity'))
                        ->relationship('opportunity', 'name')
                        ->searchable()
                        ->preload(),
                    Select::make('contact_id')
                        ->label(__('Contact'))
                        ->relationship('contact', 'name')
                        ->searchable()
                        ->preload(),
                    Select::make('company_id')
                        ->label(__('Company'))
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload(),
                    Hidden::make('user_id')
                        ->default(Auth::id()),
                ]),
        ];
    }
}
