<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Opportunity extends Model implements Sortable
{
    use SortableTrait;

    protected $fillable = [
        'pipeline_stage_id',
        'company_id',
        'contact_id',
        'name',
        'value',
        'expected_close_date',
        'notes',
        'position',
        'won_at',
        'lost_at',
        'started_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expected_close_date' => 'date',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
        'started_at' => 'datetime',
        'position' => 'integer',
    ];

    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
    ];

    public function buildSortQuery()
    {
        return static::query()->where('pipeline_stage_id', $this->pipeline_stage_id);
    }

    public function pipelineStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
