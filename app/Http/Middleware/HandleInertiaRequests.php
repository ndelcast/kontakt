<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role?->value,
                    'locale' => $user->locale,
                    'is_super_admin' => $user->isSuperAdmin(),
                    'is_admin' => $user->isAdmin(),
                ] : null,
            ],
            'currentTeam' => fn () => $user?->currentTeam ? [
                'id' => $user->currentTeam->id,
                'name' => $user->currentTeam->name,
                'slug' => $user->currentTeam->slug,
            ] : null,
            'teams' => fn () => $user?->teams->map(fn ($team) => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
            ]) ?? [],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
