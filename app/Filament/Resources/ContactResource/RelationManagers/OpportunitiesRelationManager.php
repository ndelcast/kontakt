<?php

namespace App\Filament\Resources\ContactResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OpportunitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'opportunities';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('pipeline_stage_id')
                    ->label(__('Stage'))
                    ->relationship('pipelineStage', 'name')
                    ->required(),
                Forms\Components\Select::make('company_id')
                    ->label(__('Company'))
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('value')
                    ->label(__('Value'))
                    ->numeric()
                    ->prefix('€')
                    ->default(0),
                Forms\Components\DatePicker::make('started_at')
                    ->label(__('Start date'))
                    ->default(now())
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
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->weight('bold'),
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
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('expected_close_date')
                    ->label(__('Expected close date'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
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
