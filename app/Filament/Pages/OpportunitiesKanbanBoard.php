<?php

namespace App\Filament\Pages;

use App\Models\Opportunity;
use App\Models\PipelineStage;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Filament\Support\Enums\MaxWidth;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;

class OpportunitiesKanbanBoard extends KanbanBoard
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    protected static string $model = Opportunity::class;

    protected static string $recordTitleAttribute = 'name';

    protected static string $recordStatusAttribute = 'pipeline_stage_id';

    protected static ?int $navigationSort = 3;

    protected static string $recordView = 'filament.kanban.kanban-record';

    public function getTitle(): string
    {
        return __('Kanban Board');
    }

    public static function getNavigationLabel(): string
    {
        return __('Kanban Board');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Pipeline');
    }

    protected function statuses(): Collection
    {
        $team = Filament::getTenant();

        return PipelineStage::query()
            ->when($team, fn ($q) => $q->where('team_id', $team->id))
            ->orderBy('position')
            ->withCount(['opportunities' => fn ($q) => $q->when($team, fn ($oq) => $oq->where('team_id', $team->id))])
            ->withSum(['opportunities' => fn ($q) => $q->when($team, fn ($oq) => $oq->where('team_id', $team->id))], 'value')
            ->get()
            ->map(function (PipelineStage $stage) {
                return [
                    'id' => $stage->id,
                    'title' => $stage->name . ' (' . $stage->opportunities_count . ')',
                    'color' => $stage->color,
                ];
            });
    }

    protected function records(): Collection
    {
        $team = Filament::getTenant();

        return Opportunity::with(['company', 'contact', 'pipelineStage'])
            ->when($team, fn ($q) => $q->where('team_id', $team->id))
            ->ordered()
            ->get();
    }

    public function onStatusChanged(int|string $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        $opportunity = Opportunity::find($recordId);
        $stage = PipelineStage::find($status);

        $updateData = [
            'pipeline_stage_id' => $status,
        ];

        if ($stage->is_won) {
            $updateData['won_at'] = now();
            $updateData['lost_at'] = null;
        } elseif ($stage->is_lost) {
            $updateData['lost_at'] = now();
            $updateData['won_at'] = null;
        } else {
            $updateData['won_at'] = null;
            $updateData['lost_at'] = null;
        }

        $opportunity->update($updateData);

        Opportunity::setNewOrder($toOrderedIds);
    }

    public function onSortChanged(int|string $recordId, string $status, array $orderedIds): void
    {
        Opportunity::setNewOrder($orderedIds);
    }

    protected function getEditModalFormSchema(null|int|string $recordId): array
    {
        $team = Filament::getTenant();

        return [
            TextInput::make('name')
                ->label(__('Name'))
                ->required(),
            Select::make('pipeline_stage_id')
                ->label(__('Stage'))
                ->options(
                    PipelineStage::query()
                        ->when($team, fn ($q) => $q->where('team_id', $team->id))
                        ->pluck('name', 'id')
                )
                ->required(),
            Select::make('company_id')
                ->label(__('Company'))
                ->relationship('company', 'name', fn ($query) => $query->when($team, fn ($q) => $q->where('team_id', $team->id)))
                ->searchable()
                ->preload(),
            Select::make('contact_id')
                ->label(__('Contact'))
                ->relationship('contact', 'name', fn ($query) => $query->when($team, fn ($q) => $q->where('team_id', $team->id)))
                ->searchable()
                ->preload(),
            TextInput::make('value')
                ->label(__('Value'))
                ->numeric()
                ->prefix('€'),
            DatePicker::make('started_at')
                ->label(__('Start date'))
                ->displayFormat('d/m/Y'),
            DatePicker::make('expected_close_date')
                ->label(__('Expected close date'))
                ->displayFormat('d/m/Y'),
            DatePicker::make('won_at')
                ->label(__('Won date'))
                ->displayFormat('d/m/Y'),
            Textarea::make('notes')
                ->label(__('Notes')),
        ];
    }

    protected function editRecord(int|string $recordId, array $data, array $state): void
    {
        $opportunity = Opportunity::find($recordId);

        if (isset($data['pipeline_stage_id'])) {
            $stage = PipelineStage::find($data['pipeline_stage_id']);
            if ($stage->is_won) {
                $data['won_at'] = now();
                $data['lost_at'] = null;
            } elseif ($stage->is_lost) {
                $data['lost_at'] = now();
                $data['won_at'] = null;
            }
        }

        $opportunity->update($data);
    }

    protected function getEloquentQuery(): Builder
    {
        $team = Filament::getTenant();

        return Opportunity::query()
            ->when($team, fn ($q) => $q->where('team_id', $team->id));
    }
}
