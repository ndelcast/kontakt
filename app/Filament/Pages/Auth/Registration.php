<?php

namespace App\Filament\Pages\Auth;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Registration extends BaseRegister
{
    protected ?TeamInvitation $pendingInvitation = null;

    public function mount(): void
    {
        parent::mount();

        // Check for pending invitation
        $token = session('pending_invitation_token');
        if ($token) {
            $this->pendingInvitation = TeamInvitation::where('token', $token)
                ->where('expires_at', '>', now())
                ->first();

            // Pre-fill email if invitation exists
            if ($this->pendingInvitation) {
                $this->form->fill([
                    'email' => $this->pendingInvitation->email,
                ]);
            }
        }
    }

    public function form(Form $form): Form
    {
        $hasPendingInvitation = $this->hasPendingInvitation();

        $schema = [];

        if ($hasPendingInvitation) {
            // Show invitation banner at the top
            $schema[] = Placeholder::make('invitation_banner')
                ->hiddenLabel()
                ->content(new HtmlString(
                    '<div class="rounded-lg bg-primary-50 dark:bg-primary-950 p-4 ring-1 ring-primary-200 dark:ring-primary-800">' .
                    '<div class="flex items-start gap-3">' .
                    '<div class="shrink-0 text-primary-500">' .
                    '<svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>' .
                    '</div>' .
                    '<div>' .
                    '<p class="text-sm font-medium text-primary-800 dark:text-primary-200">' .
                    __('You have been invited to join the team :team', ['team' => '<strong>' . e($this->pendingInvitation->team->name) . '</strong>']) .
                    '</p>' .
                    '<p class="mt-1 text-sm text-primary-700 dark:text-primary-300">' .
                    __('Create an account to accept the invitation and access the team.') .
                    '</p>' .
                    '</div>' .
                    '</div>' .
                    '</div>'
                ));
        }

        $schema[] = $this->getNameFormComponent();
        $schema[] = $this->getEmailFormComponent()
            ->disabled($hasPendingInvitation)
            ->dehydrated(true);
        $schema[] = $this->getPasswordFormComponent();
        $schema[] = $this->getPasswordConfirmationFormComponent();

        if (! $hasPendingInvitation) {
            // Normal registration - ask for team name
            $schema[] = TextInput::make('team_name')
                ->label(__('Team Name'))
                ->required()
                ->maxLength(255)
                ->placeholder(__('Your company or team name'));
        }

        return $form->schema($schema);
    }

    protected function handleRegistration(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Create the user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'member',
            ]);

            // Check if this is an invited user
            if ($this->hasPendingInvitation()) {
                $invitation = $this->pendingInvitation;
                $team = $invitation->team;

                // Add user to the invited team
                $user->teams()->attach($team, ['role' => $invitation->role]);
                $user->update([
                    'current_team_id' => $team->id,
                    'approved_at' => now(), // Auto-approve invited users
                ]);

                // Delete the invitation
                $invitation->delete();

                // Clear the session
                session()->forget('pending_invitation_token');
            } else {
                // Normal registration - create new team
                $baseSlug = Str::slug($data['team_name']);
                $slug = $baseSlug;
                $counter = 1;

                while (Team::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }

                $team = Team::create([
                    'name' => $data['team_name'],
                    'slug' => $slug,
                ]);

                // Attach user to team as admin
                $user->teams()->attach($team, ['role' => 'admin']);
                $user->update(['current_team_id' => $team->id]);
            }

            return $user;
        });
    }

    protected function getRedirectUrl(): string
    {
        $user = auth()->user();

        // If user is approved (invited user), redirect to team dashboard
        if ($user && $user->isApproved()) {
            $team = $user->currentTeam ?? $user->teams()->first();
            if ($team) {
                return filament()->getUrl($team);
            }
        }

        // Otherwise, redirect to pending approval page
        return route('filament.admin.auth.pending-approval');
    }

    protected function hasPendingInvitation(): bool
    {
        if ($this->pendingInvitation !== null) {
            return true;
        }

        $token = session('pending_invitation_token');
        if ($token) {
            $this->pendingInvitation = TeamInvitation::where('token', $token)
                ->where('expires_at', '>', now())
                ->first();
        }

        return $this->pendingInvitation !== null;
    }
}
