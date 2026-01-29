<?php

namespace App\Models\Concerns;

use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model) {
            if (blank($model->team_id) && filament()->hasTenancy()) {
                $tenant = filament()->getTenant();
                if ($tenant) {
                    $model->team_id = $tenant->id;
                }
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function scopeForTenant(Builder $query, ?Team $team = null): Builder
    {
        $team = $team ?? filament()->getTenant();

        if ($team) {
            return $query->where('team_id', $team->id);
        }

        return $query;
    }
}
