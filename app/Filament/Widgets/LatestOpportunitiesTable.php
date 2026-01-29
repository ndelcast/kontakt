<?php

namespace App\Filament\Widgets;

use App\Models\Opportunity;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOpportunitiesTable extends BaseWidget
{
    public function getHeading(): string
    {
        return __('Latest Opportunities');
    }

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $team = Filament::getTenant();

        return $table
            ->query(
                Opportunity::with(['pipelineStage', 'company'])
                    ->when($team, fn ($q) => $q->where('team_id', $team->id))
                    ->latest('started_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->company?->name),
                Tables\Columns\TextColumn::make('pipelineStage.name')
                    ->label(__('Stage'))
                    ->badge()
                    ->color(fn ($record) => match(true) {
                        $record->pipelineStage?->is_won => 'success',
                        $record->pipelineStage?->is_lost => 'danger',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('Value'))
                    ->money('EUR')
                    ->weight('bold')
                    ->color('success')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('started_at')
                    ->label(__('Start date'))
                    ->since()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
