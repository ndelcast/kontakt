<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Super admins always have access
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user is approved
        if (!$user->isApproved()) {
            // Allow access to pending approval page and logout
            $allowedRoutes = [
                'filament.admin.auth.pending-approval',
                'filament.admin.auth.logout',
            ];

            if (in_array($request->route()?->getName(), $allowedRoutes)) {
                return $next($request);
            }

            return redirect()->route('filament.admin.auth.pending-approval');
        }

        return $next($request);
    }
}
