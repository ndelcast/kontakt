<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OpportunityResource\Pages;
use App\Filament\Resources\OpportunityResource\RelationManagers;
use App\Models\Opportunity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OpportunityResource extends Resource
{
    protected static ?string $model = Opportunity::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('Opportunity');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Opportunities');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Pipeline');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereHas('pipelineStage', fn ($q) => $q->where('is_won', false)->where('is_lost', false))->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Opportunity Details'))
                    ->description(__('Track the deal through your sales pipeline.'))
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-tag')
                            ->placeholder('New website project'),
                        Forms\Components\Select::make('pipeline_stage_id')
                            ->label(__('Stage'))
                            ->relationship('pipelineStage', 'name')
                            ->required()
                            ->preload()
                            ->prefixIcon('heroicon-o-view-columns')
                            ->helperText(__('Current stage of this deal in the pipeline.')),
                        Forms\Components\Select::make('company_id')
                            ->label(__('Company'))
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-building-office')
                            ->reactive()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('contact_id', null)),
                        Forms\Components\Select::make('contact_id')
                            ->label(__('Contact'))
                            ->relationship('contact', 'name')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-user'),
                        Forms\Components\TextInput::make('value')
                            ->label(__('Value'))
                            ->numeric()
                            ->prefix('€')
                            ->default(0)
                            ->placeholder('10000')
                            ->helperText(__('Expected deal value in EUR.')),
                        Forms\Components\DatePicker::make('started_at')
                            ->label(__('Start date'))
                            ->default(now())
                            ->prefixIcon('heroicon-o-calendar')
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('expected_close_date')
                            ->label(__('Expected close date'))
                            ->prefixIcon('heroicon-o-calendar')
                            ->displayFormat('d/m/Y')
                            ->placeholder('Select expected close date'),
                        Forms\Components\DatePicker::make('won_at')
                            ->label(__('Won date'))
                            ->prefixIcon('heroicon-o-trophy')
                            ->displayFormat('d/m/Y'),
                        Forms\Components\Textarea::make('notes')
                            ->label(__('Notes'))
                            ->placeholder('Deal notes, next steps, etc.')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->pipelineStage?->name),
                Tables\Columns\TextColumn::make('pipelineStage.name')
                    ->label(__('Stage'))
                    ->badge()
                    ->color(fn ($record) => match(true) {
                        $record->pipelineStage?->is_won => 'success',
                        $record->pipelineStage?->is_lost => 'danger',
                        default => 'primary',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->label(__('Company'))
                    ->sortable()
                    ->icon('heroicon-o-building-office'),
                Tables\Columns\TextColumn::make('contact.name')
                    ->label(__('Contact'))
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-user'),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('Value'))
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('started_at')
                    ->label(__('Start date'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                Tables\Columns\TextColumn::make('expected_close_date')
                    ->label(__('Expected close date'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn ($record) => $record->expected_close_date ? $record->expected_close_date->diffForHumans() : null)
                    ->color(fn ($record) => $record->expected_close_date && $record->expected_close_date->isPast() && !$record->pipelineStage?->is_won && !$record->pipelineStage?->is_lost ? 'danger' : null)
                    ->icon('heroicon-o-calendar'),
                Tables\Columns\TextColumn::make('won_at')
                    ->label(__('Won date'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-trophy'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordClasses(fn (Model $record) => match (true) {
                (bool) $record->pipelineStage?->is_won => 'bg-emerald-50 dark:bg-emerald-950/20',
                (bool) $record->pipelineStage?->is_lost => 'bg-rose-50 dark:bg-rose-950/20',
                default => '',
            })
            ->filters([
                Tables\Filters\SelectFilter::make('pipeline_stage_id')
                    ->relationship('pipelineStage', 'name')
                    ->label(__('Stage'))
                    ->preload(),
                Tables\Filters\SelectFilter::make('company_id')
                    ->relationship('company', 'name')
                    ->label(__('Company'))
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('pipeline_stage_id')
                            ->label(__('Stage'))
                            ->relationship('pipelineStage', 'name')
                            ->required()
                            ->preload(),
                        Forms\Components\Select::make('company_id')
                            ->label(__('Company'))
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('contact_id')
                            ->label(__('Contact'))
                            ->relationship('contact', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('value')
                            ->label(__('Value'))
                            ->numeric()
                            ->prefix('€'),
                        Forms\Components\DatePicker::make('started_at')
                            ->label(__('Start date'))
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('expected_close_date')
                            ->label(__('Expected close date'))
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('won_at')
                            ->label(__('Won date'))
                            ->displayFormat('d/m/Y'),
                        Forms\Components\Textarea::make('notes')
                            ->label(__('Notes'))
                            ->columnSpanFull(),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOpportunities::route('/'),
            'create' => Pages\CreateOpportunity::route('/create'),
            'edit' => Pages\EditOpportunity::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }
}
