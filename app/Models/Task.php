<?php

namespace App\Models;

use App\Enums\TaskType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'team_id',
        'user_id',
        'opportunity_id',
        'contact_id',
        'company_id',
        'type',
        'title',
        'description',
        'due_date',
        'due_time',
        'completed_at',
        'outcome',
        'priority',
    ];

    protected $casts = [
        'type' => TaskType::class,
        'due_date' => 'date',
        'due_time' => 'datetime:H:i',
        'completed_at' => 'datetime',
        'priority' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->pending()
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString());
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->pending()
            ->where('due_date', now()->toDateString());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->pending()
            ->where(function ($q) {
                $q->whereNull('due_date')
                    ->orWhere('due_date', '>', now()->toDateString());
            });
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->lt(now()->startOfDay()) && !$this->completed_at;
    }

    public function isToday(): bool
    {
        return $this->due_date && $this->due_date->isToday() && !$this->completed_at;
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            2 => __('Urgent'),
            1 => __('High'),
            default => __('Normal'),
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            2 => 'danger',
            1 => 'warning',
            default => 'gray',
        };
    }
}
