<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function showRegistrationForm(Request $request)
    {
        $invitation = null;
        $token = session('pending_invitation_token');

        if ($token) {
            $inv = TeamInvitation::where('token', $token)
                ->where('expires_at', '>', now())
                ->with('team:id,name')
                ->first();

            if ($inv) {
                $invitation = [
                    'email' => $inv->email,
                    'team_name' => $inv->team->name,
                ];
            }
        }

        return Inertia::render('Auth/Register', [
            'invitation' => $invitation,
        ]);
    }

    public function register(Request $request)
    {
        $hasInvitation = $this->getPendingInvitation() !== null;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];

        if (! $hasInvitation) {
            $rules['team_name'] = ['required', 'string', 'max:255'];
        }

        $data = $request->validate($rules);

        $user = DB::transaction(function () use ($data, $hasInvitation) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'member',
            ]);

            $invitation = $this->getPendingInvitation();

            if ($invitation) {
                $team = $invitation->team;

                $user->teams()->attach($team, ['role' => $invitation->role]);
                $user->update([
                    'current_team_id' => $team->id,
                    'approved_at' => now(),
                ]);

                $invitation->delete();
                session()->forget('pending_invitation_token');
            } else {
                $baseSlug = Str::slug($data['team_name']);
                $slug = $baseSlug;
                $counter = 1;

                while (Team::where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter++;
                }

                $team = Team::create([
                    'name' => $data['team_name'],
                    'slug' => $slug,
                ]);

                $user->teams()->attach($team, ['role' => 'admin']);
                $user->update(['current_team_id' => $team->id]);
            }

            return $user;
        });

        Auth::login($user);

        if ($user->isApproved()) {
            return redirect()->route('app.dashboard');
        }

        return redirect()->route('pending-approval');
    }

    protected function getPendingInvitation(): ?TeamInvitation
    {
        $token = session('pending_invitation_token');

        if (! $token) {
            return null;
        }

        return TeamInvitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
    }
}
