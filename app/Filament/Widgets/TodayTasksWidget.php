<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TodayTasksWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return __("Today's Tasks");
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->forUser(Auth::id())
                    ->where(function (Builder $query) {
                        $query->overdue()
                            ->orWhere(function (Builder $q) {
                                $q->today();
                            });
                    })
                    ->orderByRaw("CASE WHEN due_date < ? THEN 0 ELSE 1 END", [now()->toDateString()])
                    ->orderBy('due_time')
                    ->orderByDesc('priority')
            )
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->weight('bold')
                    ->description(fn ($record) => $record->opportunity?->name ?? $record->contact?->name ?? $record->company?->name)
                    ->limit(40),
                Tables\Columns\TextColumn::make('due_date')
                    ->label(__('Due date'))
                    ->date('d/m/Y')
                    ->description(fn ($record) => $record->due_time?->format('H:i'))
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : 'warning')
                    ->icon('heroicon-o-calendar'),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->priority_label)
                    ->color(fn ($record) => $record->priority_color),
            ])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->label(__('Complete'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Task $record) {
                        $record->update(['completed_at' => now()]);
                    }),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading(__('No tasks for today'))
            ->emptyStateDescription(__('You have no pending tasks for today. Great job!'))
            ->emptyStateIcon('heroicon-o-check-circle')
            ->headerActions([
                Tables\Actions\Action::make('my-day')
                    ->label(__('My Day'))
                    ->icon('heroicon-o-sun')
                    ->url(fn () => route('filament.admin.pages.my-day'))
                    ->color('primary'),
            ]);
    }
}
