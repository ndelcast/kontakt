<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PipelineStageResource\Pages;
use App\Models\PipelineStage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PipelineStageResource extends Resource
{
    protected static ?string $model = PipelineStage::class;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('Pipeline Stage');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Pipeline Stages');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Pipeline');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Stage Configuration'))
                    ->description(__('Define pipeline stages and their win probability.'))
                    ->icon('heroicon-o-view-columns')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-tag')
                            ->placeholder('Qualification'),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon('heroicon-o-link')
                            ->placeholder('qualification')
                            ->helperText(__('URL-friendly identifier for this stage.')),
                        Forms\Components\ColorPicker::make('color')
                            ->helperText(__('Color used on the Kanban board.')),
                        Forms\Components\TextInput::make('probability')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required()
                            ->prefixIcon('heroicon-o-chart-pie')
                            ->placeholder('50')
                            ->helperText(__('Likelihood of closing deals in this stage.')),
                        Forms\Components\Toggle::make('is_won')
                            ->label(__('Is Won Stage'))
                            ->onColor('success')
                            ->helperText(__('Mark this as the winning/closed-won stage.')),
                        Forms\Components\Toggle::make('is_lost')
                            ->label(__('Is Lost Stage'))
                            ->onColor('danger')
                            ->helperText(__('Mark this as the lost/closed-lost stage.')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('position')
            ->defaultSort('position')
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label(''),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->probability . '% probability'),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('probability')
                    ->suffix('%')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 75 => 'success',
                        $state >= 40 => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_won')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('is_lost')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('opportunities_count')
                    ->counts('opportunities')
                    ->label('Deals')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([])
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
            'index' => Pages\ListPipelineStages::route('/'),
            'create' => Pages\CreatePipelineStage::route('/create'),
            'edit' => Pages\EditPipelineStage::route('/{record}/edit'),
        ];
    }
}
