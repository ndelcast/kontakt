<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Pages\MyDay;
use App\Filament\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('my-day')
                ->label(__('My Day'))
                ->icon('heroicon-o-sun')
                ->url(fn () => MyDay::getUrl()),
            Actions\CreateAction::make(),
        ];
    }
}
