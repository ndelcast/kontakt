<?php

namespace App\Filament\Resources\OpportunityResource\RelationManagers;

use App\Enums\TaskType;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('Tasks');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label(__('Type'))
                    ->options(TaskType::class)
                    ->required()
                    ->default(TaskType::FollowUp),
                Forms\Components\TextInput::make('title')
                    ->label(__('Title'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label(__('Description'))
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('due_date')
                    ->label(__('Due date'))
                    ->displayFormat('d/m/Y')
                    ->default(now()),
                Forms\Components\TimePicker::make('due_time')
                    ->label(__('Due time'))
                    ->seconds(false),
                Forms\Components\Select::make('priority')
                    ->label(__('Priority'))
                    ->options([
                        0 => __('Normal'),
                        1 => __('High'),
                        2 => __('Urgent'),
                    ])
                    ->default(0),
                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->weight('bold')
                    ->limit(40),
                Tables\Columns\TextColumn::make('due_date')
                    ->label(__('Due date'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn ($record) => $record->due_time?->format('H:i'))
                    ->color(fn ($record) => match(true) {
                        $record->completed_at !== null => 'gray',
                        $record->isOverdue() => 'danger',
                        $record->isToday() => 'warning',
                        default => null,
                    })
                    ->icon('heroicon-o-calendar'),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->priority_label)
                    ->color(fn ($record) => $record->priority_color),
                Tables\Columns\IconColumn::make('completed_at')
                    ->label(__('Completed'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->defaultSort('due_date', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->label(__('Complete'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('Mark as completed'))
                    ->modalDescription(__('What was the outcome?'))
                    ->form([
                        Forms\Components\Textarea::make('outcome')
                            ->label(__('Outcome'))
                            ->placeholder('What was the result?'),
                    ])
                    ->action(function (Task $record, array $data) {
                        $record->update([
                            'completed_at' => now(),
                            'outcome' => $data['outcome'] ?? null,
                        ]);
                    })
                    ->visible(fn (Task $record) => !$record->completed_at),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
