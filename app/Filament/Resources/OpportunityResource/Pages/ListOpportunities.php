<?php

namespace App\Filament\Resources\OpportunityResource\Pages;

use App\Filament\Pages\OpportunitiesKanbanBoard;
use App\Filament\Resources\OpportunityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOpportunities extends ListRecords
{
    protected static string $resource = OpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('kanban')
                ->label(__('Kanban Board'))
                ->icon('heroicon-o-view-columns')
                ->url(fn () => OpportunitiesKanbanBoard::getUrl()),
            Actions\CreateAction::make(),
        ];
    }
}
