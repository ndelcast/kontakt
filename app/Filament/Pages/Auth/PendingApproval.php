<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Facades\Filament;
use Filament\Pages\SimplePage;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class PendingApproval extends SimplePage
{
    use WithRateLimiting;

    protected static string $view = 'filament.pages.auth.pending-approval';

    public function getTitle(): string|Htmlable
    {
        return __('Account Pending Approval');
    }

    public function getHeading(): string|Htmlable
    {
        return __('Account Pending Approval');
    }

    public static function getSlug(): string
    {
        return 'auth/pending-approval';
    }

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            redirect()->to(Filament::getLoginUrl());
            return;
        }

        // If user is approved, redirect to their team dashboard
        if ($user->isApproved()) {
            $team = $user->currentTeam ?? $user->teams()->first();

            if ($team) {
                redirect()->intended(Filament::getUrl($team));
            } else {
                redirect()->intended(Filament::getUrl());
            }
        }
    }

    public function logout(): void
    {
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(Filament::getLoginUrl());
    }
}
