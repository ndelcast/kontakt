<?php

namespace App\Filament\Resources;

use App\Enums\TaskType;
use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getModelLabel(): string
    {
        return __('Task');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Tasks');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Activities');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::forUser(Auth::id())->pending()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $overdueCount = static::getModel()::forUser(Auth::id())->overdue()->count();
        return $overdueCount > 0 ? 'danger' : 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Task Details'))
                    ->description(__('Define your task or reminder.'))
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label(__('Type'))
                            ->options(TaskType::class)
                            ->required()
                            ->prefixIcon('heroicon-o-tag')
                            ->default(TaskType::Call),
                        Forms\Components\TextInput::make('title')
                            ->label(__('Title'))
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-pencil')
                            ->placeholder('Follow up on proposal'),
                        Forms\Components\Textarea::make('description')
                            ->label(__('Description'))
                            ->placeholder('Additional notes about this task...')
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('due_date')
                            ->label(__('Due date'))
                            ->prefixIcon('heroicon-o-calendar')
                            ->displayFormat('d/m/Y')
                            ->default(now()),
                        Forms\Components\TimePicker::make('due_time')
                            ->label(__('Due time'))
                            ->prefixIcon('heroicon-o-clock')
                            ->seconds(false),
                        Forms\Components\Select::make('priority')
                            ->label(__('Priority'))
                            ->options([
                                0 => __('Normal'),
                                1 => __('High'),
                                2 => __('Urgent'),
                            ])
                            ->default(0)
                            ->prefixIcon('heroicon-o-flag'),
                    ])->columns(2),

                Forms\Components\Section::make(__('Related To'))
                    ->description(__('Link this task to your contacts or deals.'))
                    ->icon('heroicon-o-link')
                    ->schema([
                        Forms\Components\Select::make('opportunity_id')
                            ->label(__('Opportunity'))
                            ->relationship('opportunity', 'name')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-currency-dollar'),
                        Forms\Components\Select::make('contact_id')
                            ->label(__('Contact'))
                            ->relationship('contact', 'name')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-user'),
                        Forms\Components\Select::make('company_id')
                            ->label(__('Company'))
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-building-office'),
                    ])->columns(3),

                Forms\Components\Section::make(__('Completion'))
                    ->description(__('Mark task as completed and record the outcome.'))
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label(__('Completed'))
                            ->prefixIcon('heroicon-o-check-circle')
                            ->displayFormat('d/m/Y H:i'),
                        Forms\Components\Textarea::make('outcome')
                            ->label(__('Outcome'))
                            ->placeholder('What was the result of this task?')
                            ->columnSpanFull(),
                    ])->collapsed(),

                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->forUser(Auth::id()))
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->opportunity?->name ?? $record->company?->name ?? $record->contact?->name),
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
                    ->color(fn ($record) => $record->priority_color)
                    ->sortable(),
                Tables\Columns\TextColumn::make('opportunity.name')
                    ->label(__('Opportunity'))
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-currency-dollar'),
                Tables\Columns\TextColumn::make('contact.name')
                    ->label(__('Contact'))
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-user'),
                Tables\Columns\TextColumn::make('company.name')
                    ->label(__('Company'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-building-office'),
                Tables\Columns\IconColumn::make('completed_at')
                    ->label(__('Completed'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('due_date', 'asc')
            ->recordClasses(fn (Model $record) => match (true) {
                (bool) $record->completed_at => 'opacity-50',
                $record->isOverdue() => 'bg-rose-50 dark:bg-rose-950/20',
                $record->isToday() => 'bg-amber-50 dark:bg-amber-950/20',
                default => '',
            })
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(TaskType::class)
                    ->label(__('Type')),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'completed' => __('Completed'),
                        'overdue' => __('Overdue'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value']) {
                        'pending' => $query->pending(),
                        'completed' => $query->completed(),
                        'overdue' => $query->overdue(),
                        default => $query,
                    }),
                Tables\Filters\SelectFilter::make('priority')
                    ->label(__('Priority'))
                    ->options([
                        0 => __('Normal'),
                        1 => __('High'),
                        2 => __('Urgent'),
                    ]),
                Tables\Filters\SelectFilter::make('opportunity_id')
                    ->relationship('opportunity', 'name')
                    ->label(__('Opportunity'))
                    ->searchable()
                    ->preload(),
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
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('type')
                            ->label(__('Type'))
                            ->options(TaskType::class)
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label(__('Title'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label(__('Description')),
                        Forms\Components\DatePicker::make('due_date')
                            ->label(__('Due date'))
                            ->displayFormat('d/m/Y'),
                        Forms\Components\TimePicker::make('due_time')
                            ->label(__('Due time'))
                            ->seconds(false),
                        Forms\Components\Select::make('priority')
                            ->label(__('Priority'))
                            ->options([
                                0 => __('Normal'),
                                1 => __('High'),
                                2 => __('Urgent'),
                            ]),
                        Forms\Components\Select::make('opportunity_id')
                            ->label(__('Opportunity'))
                            ->relationship('opportunity', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('contact_id')
                            ->label(__('Contact'))
                            ->relationship('contact', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('company_id')
                            ->label(__('Company'))
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description'];
    }
}
