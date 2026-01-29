<?php

namespace App\Http\Controllers;

use App\Models\TeamInvitation;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamInvitationController extends Controller
{
    public function accept(Request $request, string $token)
    {
        $invitation = TeamInvitation::where('token', $token)->first();

        if (! $invitation) {
            return redirect()->route('filament.admin.auth.login')
                ->with('error', __('Invalid invitation link.'));
        }

        if ($invitation->isExpired()) {
            $invitation->delete();
            return redirect()->route('filament.admin.auth.login')
                ->with('error', __('This invitation has expired.'));
        }

        // If user is logged in
        if (Auth::check()) {
            $user = Auth::user();

            // Check if the invitation email matches the logged-in user
            if ($user->email !== $invitation->email) {
                Auth::logout();
                return redirect()->route('team-invitation.accept', ['token' => $token])
                    ->with('warning', __('Please log in with the email address the invitation was sent to.'));
            }

            return $this->addUserToTeam($user, $invitation);
        }

        // Check if user with this email exists
        $existingUser = User::where('email', $invitation->email)->first();

        if ($existingUser) {
            // User exists, redirect to login with invitation token
            session(['pending_invitation_token' => $token]);
            return redirect()->route('filament.admin.auth.login')
                ->with('info', __('Please log in to accept the invitation.'));
        }

        // User doesn't exist, redirect to register with invitation token
        session(['pending_invitation_token' => $token]);
        return redirect()->route('filament.admin.auth.register')
            ->with('info', __('Please create an account to accept the invitation.'));
    }

    protected function addUserToTeam(User $user, TeamInvitation $invitation)
    {
        $team = $invitation->team;

        // Check if already a member
        if ($user->teams()->where('teams.id', $team->id)->exists()) {
            $invitation->delete();
            return redirect(Filament::getUrl($team))
                ->with('info', __('You are already a member of this team.'));
        }

        // Add user to team
        $user->teams()->attach($team, ['role' => $invitation->role]);

        // Set as current team if user doesn't have one
        if (! $user->current_team_id) {
            $user->update(['current_team_id' => $team->id]);
        }

        // Auto-approve user if not already approved
        if (! $user->isApproved()) {
            $user->approve();
        }

        // Delete the invitation
        $invitation->delete();

        return redirect(Filament::getUrl($team))
            ->with('success', __('You have joined the team!'));
    }
}
