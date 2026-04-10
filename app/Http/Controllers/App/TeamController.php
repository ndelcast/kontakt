<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function members(): Response
    {
        $team = Auth::user()->currentTeam;
        abort_unless($team, 404);

        $members = $team->users()->get()->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->pivot->role,
            'joined_at' => $u->pivot->created_at?->toDateString(),
        ]);

        $invitations = $team->invitations()
            ->where('expires_at', '>', now())
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'email' => $i->email,
                'role' => $i->role,
                'expires_at' => $i->expires_at->toDateString(),
                'url' => $i->getAcceptUrl(),
            ]);

        $isAdmin = Auth::user()->isSuperAdmin()
            || Auth::user()->isTeamAdmin($team);

        return Inertia::render('Team/Members', [
            'members' => $members,
            'invitations' => $invitations,
            'isAdmin' => $isAdmin,
            'team' => ['id' => $team->id, 'name' => $team->name],
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $team = Auth::user()->currentTeam;
        abort_unless($team, 404);

        $validated = $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:admin,member',
        ]);

        $invitation = TeamInvitation::createForTeam(
            $team,
            $validated['email'],
            $validated['role'],
        );

        // Send notification if the class exists
        if (class_exists(\App\Notifications\TeamInvitationNotification::class)) {
            Notification::route('mail', $validated['email'])
                ->notify(new TeamInvitationNotification($invitation));
        }

        return back()->with('success', __('Invitation sent.'));
    }

    public function cancelInvitation(TeamInvitation $invitation): RedirectResponse
    {
        abort_unless($invitation->team_id === Auth::user()->current_team_id, 403);
        $invitation->delete();

        return back()->with('success', __('Invitation cancelled.'));
    }

    public function updateMemberRole(Request $request, Team $team, int $userId): RedirectResponse
    {
        abort_unless($team->id === Auth::user()->current_team_id, 403);

        $validated = $request->validate([
            'role' => 'required|in:admin,member',
        ]);

        $team->users()->updateExistingPivot($userId, ['role' => $validated['role']]);

        return back()->with('success', __('Role updated.'));
    }

    public function removeMember(Team $team, int $userId): RedirectResponse
    {
        abort_unless($team->id === Auth::user()->current_team_id, 403);
        abort_if($userId === Auth::id(), 403);

        $user = \App\Models\User::findOrFail($userId);
        $user->leaveTeam($team);

        return back()->with('success', __('Member removed.'));
    }

    public function switchTeam(Team $team): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->teams()->whereKey($team)->exists(), 403);

        $user->switchTeam($team);

        return redirect()->route('app.dashboard')->with('success', __('Team switched.'));
    }

    public function create(): Response
    {
        return Inertia::render('Team/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);

        $team = Team::create($validated);
        Auth::user()->joinTeam($team, 'admin');
        Auth::user()->switchTeam($team);

        return redirect()->route('app.dashboard')->with('success', __('Team created.'));
    }

    public function edit(): Response
    {
        $team = Auth::user()->currentTeam;
        abort_unless($team, 404);

        return Inertia::render('Team/Edit', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'description' => $team->description,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $team = Auth::user()->currentTeam;
        abort_unless($team, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:teams,slug,' . $team->id,
            'description' => 'nullable|string',
        ]);

        $team->update($validated);

        return back()->with('success', __('Team updated.'));
    }
}
