<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use App\Models\Contact;
use App\Models\PipelineStage;
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
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('pipeline_stage_id')
                    ->relationship('pipelineStage', 'name')
                    ->required(),
                Forms\Components\Select::make('contact_id')
                    ->label(__('Contact'))
                    ->options(fn (RelationManager $livewire) => Contact::where('company_id', $livewire->ownerRecord->id)->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\TextInput::make('value')
                    ->numeric()
                    ->prefix('$')
                    ->default(0),
                Forms\Components\DatePicker::make('expected_close_date'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('pipelineStage.name')
                    ->badge()
                    ->color(fn ($record) => match(true) {
                        $record->pipelineStage?->is_won => 'success',
                        $record->pipelineStage?->is_lost => 'danger',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('expected_close_date')
                    ->date()
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
