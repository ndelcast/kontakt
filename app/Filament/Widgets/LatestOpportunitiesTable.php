<?php

namespace App\Filament\Widgets;

use App\Models\Opportunity;
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
        return $table
            ->query(
                Opportunity::with(['pipelineStage', 'company'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->company?->name),
                Tables\Columns\TextColumn::make('pipelineStage.name')
                    ->badge()
                    ->color(fn ($record) => match(true) {
                        $record->pipelineStage?->is_won => 'success',
                        $record->pipelineStage?->is_lost => 'danger',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->money('USD')
                    ->weight('bold')
                    ->color('success')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
