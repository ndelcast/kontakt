<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'role',
        'approved_at',
        'approved_by',
        'current_team_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'approved_at' => 'datetime',
            'role' => UserRole::class,
        ];
    }

    // Relationships

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'approved_by');
    }

    // Role methods

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin || $this->isSuperAdmin();
    }

    public function isTeamAdmin(?Team $team = null): bool
    {
        $team = $team ?? $this->currentTeam;

        if (!$team) {
            return false;
        }

        return $this->teams()
            ->whereKey($team)
            ->wherePivot('role', 'admin')
            ->exists();
    }

    public function isTeamMember(?Team $team = null): bool
    {
        $team = $team ?? $this->currentTeam;

        if (!$team) {
            return false;
        }

        return $this->teams()->whereKey($team)->exists();
    }

    public function getTeamRole(?Team $team = null): ?string
    {
        $team = $team ?? $this->currentTeam;

        if (!$team) {
            return null;
        }

        return $this->teams()
            ->whereKey($team)
            ->first()
            ?->pivot
            ?->role;
    }

    // Approval methods

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function isPending(): bool
    {
        return !$this->isApproved();
    }

    public function approve(?User $approver = null): void
    {
        $this->update([
            'approved_at' => now(),
            'approved_by' => $approver?->id,
        ]);
    }

    public function revoke(): void
    {
        $this->update([
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    // Team management methods

    public function joinTeam(Team $team, string $role = 'member'): void
    {
        if (!$this->teams()->whereKey($team)->exists()) {
            $this->teams()->attach($team, ['role' => $role]);
        }

        if (!$this->current_team_id) {
            $this->update(['current_team_id' => $team->id]);
        }
    }

    public function leaveTeam(Team $team): void
    {
        $this->teams()->detach($team);

        if ($this->current_team_id === $team->id) {
            $this->update([
                'current_team_id' => $this->teams()->first()?->id,
            ]);
        }
    }

    public function switchTeam(Team $team): void
    {
        if ($this->teams()->whereKey($team)->exists()) {
            $this->update(['current_team_id' => $team->id]);
        }
    }
}
