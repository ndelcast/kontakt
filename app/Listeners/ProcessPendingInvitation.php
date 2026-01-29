<?php

namespace App\Listeners;

use App\Models\TeamInvitation;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Login;

class ProcessPendingInvitation
{
    public function handle(Login $event): void
    {
        $token = session('pending_invitation_token');

        if (! $token) {
            return;
        }

        $invitation = TeamInvitation::where('token', $token)->first();

        if (! $invitation || $invitation->isExpired()) {
            session()->forget('pending_invitation_token');
            return;
        }

        $user = $event->user;

        // Verify email matches
        if ($user->email !== $invitation->email) {
            session()->forget('pending_invitation_token');
            return;
        }

        $team = $invitation->team;

        // Add user to team if not already a member
        if (! $user->teams()->where('teams.id', $team->id)->exists()) {
            $user->teams()->attach($team, ['role' => $invitation->role]);
        }

        // Set as current team
        $user->update(['current_team_id' => $team->id]);

        // Auto-approve user
        if (! $user->isApproved()) {
            $user->approve();
        }

        // Delete the invitation
        $invitation->delete();

        // Clear the session
        session()->forget('pending_invitation_token');

        // Set intended URL to the team dashboard
        session()->put('url.intended', Filament::getUrl($team));
    }
}
