<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TeamInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'email',
        'role',
        'locale',
        'token',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public static function createForTeam(Team $team, string $email, string $role = 'member', ?string $locale = null, int $expiresInDays = 7): static
    {
        return static::create([
            'team_id' => $team->id,
            'email' => $email,
            'role' => $role,
            'locale' => $locale ?? app()->getLocale(),
            'token' => static::generateToken(),
            'expires_at' => now()->addDays($expiresInDays),
        ]);
    }

    public function getAcceptUrl(): string
    {
        return route('team-invitation.accept', ['token' => $this->token]);
    }
}
