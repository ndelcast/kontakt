<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 100;

    // Disable tenant scoping - Super Admins need to see all users
    protected static bool $isScopedToTenant = false;

    public static function getNavigationLabel(): string
    {
        return __('Users');
    }

    public static function getModelLabel(): string
    {
        return __('User');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Users');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('User Information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('role')
                            ->label(__('Role'))
                            ->options(UserRole::class)
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('locale')
                            ->label(__('Language'))
                            ->options([
                                'en' => 'English',
                                'fr' => 'Français',
                                'es' => 'Español',
                            ])
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('Status'))
                    ->schema([
                        Forms\Components\Placeholder::make('approved_status')
                            ->label(__('Approval Status'))
                            ->content(fn (User $record): string =>
                                $record->isApproved()
                                    ? __('Approved') . ' - ' . $record->approved_at->format('Y-m-d H:i')
                                    : __('Pending Approval')
                            )
                            ->visibleOn('edit'),
                        Forms\Components\Select::make('current_team_id')
                            ->label(__('Current Team'))
                            ->relationship('currentTeam', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false),
                    ]),

                Forms\Components\Section::make(__('Password'))
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label(__('Password'))
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),
                    ])
                    ->collapsible()
                    ->collapsed(fn (string $context): bool => $context === 'edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->label(__('Role'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('teams_count')
                    ->label(__('Teams'))
                    ->counts('teams')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\IconColumn::make('approved_at')
                    ->label(__('Approved'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->getStateUsing(fn (User $record): bool => $record->isApproved()),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label(__('Role'))
                    ->options(UserRole::class),
                Tables\Filters\TernaryFilter::make('approved')
                    ->label(__('Approved'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('approved_at'),
                        false: fn (Builder $query) => $query->whereNull('approved_at'),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => !$record->isApproved())
                    ->requiresConfirmation()
                    ->modalHeading(__('Approve User'))
                    ->modalDescription(fn (User $record): string =>
                        __('Are you sure you want to approve :name?', ['name' => $record->name])
                    )
                    ->action(fn (User $record) => $record->approve(auth()->user())),
                Tables\Actions\Action::make('revoke')
                    ->label(__('Revoke'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->isApproved() && !$record->isSuperAdmin())
                    ->requiresConfirmation()
                    ->modalHeading(__('Revoke Approval'))
                    ->modalDescription(fn (User $record): string =>
                        __('Are you sure you want to revoke approval for :name?', ['name' => $record->name])
                    )
                    ->action(fn (User $record) => $record->revoke()),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label(__('Approve Selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->approve(auth()->user()));
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
