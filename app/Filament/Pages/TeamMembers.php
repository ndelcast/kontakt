<?php

namespace App\Filament\Pages;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class TeamMembers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static string $view = 'filament.pages.team-members';
    protected static ?int $navigationSort = 99;

    public function getPendingInvitations()
    {
        $team = Filament::getTenant();

        if (! $team) {
            return collect();
        }

        return TeamInvitation::where('team_id', $team->id)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function cancelInvitation(int $invitationId): void
    {
        $team = Filament::getTenant();

        TeamInvitation::where('id', $invitationId)
            ->where('team_id', $team->id)
            ->delete();

        Notification::make()
            ->success()
            ->title(__('Invitation cancelled'))
            ->send();
    }

    public function copyInvitationLink(string $url): void
    {
        Notification::make()
            ->success()
            ->title(__('Link copied to clipboard'))
            ->send();
    }

    public function resendInvitation(int $invitationId): void
    {
        $team = Filament::getTenant();

        $invitation = TeamInvitation::where('id', $invitationId)
            ->where('team_id', $team->id)
            ->first();

        if (! $invitation) {
            return;
        }

        // Send email notification
        NotificationFacade::route('mail', $invitation->email)
            ->notify(new TeamInvitationNotification($invitation));

        Notification::make()
            ->success()
            ->title(__('Invitation resent'))
            ->body(__('An email has been sent to :email', ['email' => $invitation->email]))
            ->send();
    }

    public static function getNavigationLabel(): string
    {
        return __('Team Members');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Team');
    }

    public function getTitle(): string
    {
        return __('Team Members');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        $team = Filament::getTenant();

        if (!$user || !$team) {
            return false;
        }

        // Team admins or super admins can access
        return $user->isSuperAdmin() || $user->isTeamAdmin($team);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('invite')
                ->label(__('Invite Member'))
                ->icon('heroicon-o-user-plus')
                ->form([
                    TextInput::make('email')
                        ->label(__('Email'))
                        ->email()
                        ->required()
                        ->maxLength(255),
                    Select::make('role')
                        ->label(__('Role'))
                        ->options([
                            'admin' => __('Admin'),
                            'member' => __('Member'),
                        ])
                        ->required()
                        ->default('member')
                        ->native(false),
                ])
                ->action(function (array $data): void {
                    $team = Filament::getTenant();

                    // Check if user already in team
                    if ($team->users()->where('email', $data['email'])->exists()) {
                        Notification::make()
                            ->danger()
                            ->title(__('User already in team'))
                            ->send();
                        return;
                    }

                    // Check if invitation already exists
                    if (TeamInvitation::where('team_id', $team->id)
                        ->where('email', $data['email'])
                        ->where('expires_at', '>', now())
                        ->exists()
                    ) {
                        Notification::make()
                            ->danger()
                            ->title(__('Invitation already sent'))
                            ->send();
                        return;
                    }

                    // Create invitation
                    $invitation = TeamInvitation::createForTeam($team, $data['email'], $data['role']);

                    // Send email notification
                    NotificationFacade::route('mail', $data['email'])
                        ->notify(new TeamInvitationNotification($invitation));

                    Notification::make()
                        ->success()
                        ->title(__('Invitation sent'))
                        ->body(__('An email has been sent to :email', ['email' => $data['email']]))
                        ->send();
                }),
        ];
    }

    public function table(Table $table): Table
    {
        $team = Filament::getTenant();

        return $table
            ->query(
                User::query()
                    ->whereHas('teams', fn (Builder $query) => $query->where('teams.id', $team?->id))
            )
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('team_role')
                    ->label(__('Team Role'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'warning',
                        default => 'gray',
                    })
                    ->getStateUsing(fn (User $record): string =>
                        $record->teams()
                            ->where('teams.id', $team?->id)
                            ->first()
                            ?->pivot
                            ?->role ?? 'member'
                    )
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => __('Admin'),
                        default => __('Member'),
                    }),
                TextColumn::make('created_at')
                    ->label(__('Joined'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                TableAction::make('make_admin')
                    ->label(__('Make Admin'))
                    ->icon('heroicon-o-shield-check')
                    ->color('warning')
                    ->visible(function (User $record) use ($team): bool {
                        $role = $record->teams()
                            ->where('teams.id', $team?->id)
                            ->first()
                            ?->pivot
                            ?->role;
                        return $role !== 'admin';
                    })
                    ->requiresConfirmation()
                    ->action(function (User $record) use ($team): void {
                        $record->teams()->updateExistingPivot($team->id, ['role' => 'admin']);
                        Notification::make()->success()->title(__('Role updated'))->send();
                    }),
                TableAction::make('make_member')
                    ->label(__('Make Member'))
                    ->icon('heroicon-o-user')
                    ->color('gray')
                    ->visible(function (User $record) use ($team): bool {
                        $role = $record->teams()
                            ->where('teams.id', $team?->id)
                            ->first()
                            ?->pivot
                            ?->role;
                        // Don't allow demoting self or if already member
                        return $role === 'admin' && $record->id !== auth()->id();
                    })
                    ->requiresConfirmation()
                    ->action(function (User $record) use ($team): void {
                        $record->teams()->updateExistingPivot($team->id, ['role' => 'member']);
                        Notification::make()->success()->title(__('Role updated'))->send();
                    }),
                DeleteAction::make('remove')
                    ->label(__('Remove'))
                    ->visible(fn (User $record): bool => $record->id !== auth()->id())
                    ->modalHeading(__('Remove from Team'))
                    ->modalDescription(fn (User $record): string =>
                        __('Are you sure you want to remove :name from this team?', ['name' => $record->name])
                    )
                    ->action(function (User $record) use ($team): void {
                        $record->leaveTeam($team);
                        Notification::make()->success()->title(__('Member removed'))->send();
                    }),
            ])
            ->defaultSort('name');
    }
}
