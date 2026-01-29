<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class PipelineStage extends Model implements Sortable
{
    use BelongsToTenant, SortableTrait;

    protected $fillable = [
        'team_id',
        'name',
        'slug',
        'color',
        'probability',
        'position',
        'is_won',
        'is_lost',
    ];

    protected $casts = [
        'probability' => 'integer',
        'position' => 'integer',
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
    ];

    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
    ];

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }
}
