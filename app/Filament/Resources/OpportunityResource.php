<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OpportunityResource\Pages;
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
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-tag')
                            ->placeholder('New website project'),
                        Forms\Components\Select::make('pipeline_stage_id')
                            ->relationship('pipelineStage', 'name')
                            ->required()
                            ->preload()
                            ->prefixIcon('heroicon-o-view-columns')
                            ->helperText(__('Current stage of this deal in the pipeline.')),
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-building-office')
                            ->reactive()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('contact_id', null)),
                        Forms\Components\Select::make('contact_id')
                            ->relationship('contact', 'name')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-user'),
                        Forms\Components\TextInput::make('value')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->placeholder('10000')
                            ->helperText(__('Expected deal value in USD.')),
                        Forms\Components\DatePicker::make('expected_close_date')
                            ->prefixIcon('heroicon-o-calendar')
                            ->displayFormat('M d, Y')
                            ->placeholder('Select expected close date'),
                        Forms\Components\Textarea::make('notes')
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
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->pipelineStage?->name),
                Tables\Columns\TextColumn::make('pipelineStage.name')
                    ->badge()
                    ->color(fn ($record) => match(true) {
                        $record->pipelineStage?->is_won => 'success',
                        $record->pipelineStage?->is_lost => 'danger',
                        default => 'primary',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->sortable()
                    ->icon('heroicon-o-building-office'),
                Tables\Columns\TextColumn::make('contact.name')
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-user'),
                Tables\Columns\TextColumn::make('value')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('expected_close_date')
                    ->date()
                    ->sortable()
                    ->description(fn ($record) => $record->expected_close_date ? $record->expected_close_date->diffForHumans() : null)
                    ->color(fn ($record) => $record->expected_close_date && $record->expected_close_date->isPast() && !$record->pipelineStage?->is_won && !$record->pipelineStage?->is_lost ? 'danger' : null)
                    ->icon('heroicon-o-calendar'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
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
                Tables\Actions\EditAction::make(),
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
