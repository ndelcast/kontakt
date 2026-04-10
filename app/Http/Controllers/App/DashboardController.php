<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\PipelineStage;
use App\Models\Task;
use Flowframe\Trend\Trend;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $teamId = Auth::user()->current_team_id;

        $pipelineStats = $this->getPipelineStats($teamId);
        $conversionStats = $this->getConversionStats($teamId);
        $incomingLeads = $this->getIncomingLeads($teamId);
        $revenueOverTime = $this->getRevenueOverTime($teamId);
        $wonLostOverTime = $this->getWonLostOverTime($teamId);
        $pipelineByStage = $this->getPipelineByStage($teamId);
        $latestOpportunities = $this->getLatestOpportunities($teamId);
        $todayTasks = $this->getTodayTasks($teamId);

        return Inertia::render('Dashboard', compact(
            'pipelineStats',
            'conversionStats',
            'incomingLeads',
            'revenueOverTime',
            'wonLostOverTime',
            'pipelineByStage',
            'latestOpportunities',
            'todayTasks',
        ));
    }

    private function getPipelineStats(int $teamId): array
    {
        $openOpportunities = Opportunity::where('team_id', $teamId)
            ->whereNull('won_at')->whereNull('lost_at');

        $activeCount = Contact::where('team_id', $teamId)->count()
            + (clone $openOpportunities)->count();

        $pipelineValue = (clone $openOpportunities)->sum('value');

        $weightedValue = Opportunity::where('opportunities.team_id', $teamId)
            ->whereNull('won_at')->whereNull('lost_at')
            ->join('pipeline_stages', 'opportunities.pipeline_stage_id', '=', 'pipeline_stages.id')
            ->selectRaw('SUM(opportunities.value * pipeline_stages.probability / 100) as total')
            ->value('total') ?? 0;

        $wonThisMonth = Opportunity::where('team_id', $teamId)
            ->whereNotNull('won_at')
            ->whereMonth('won_at', now()->month)
            ->whereYear('won_at', now()->year);

        return [
            'active_count' => $activeCount,
            'pipeline_value' => round($pipelineValue, 2),
            'weighted_value' => round($weightedValue, 2),
            'won_this_month' => $wonThisMonth->count(),
            'won_this_month_value' => round($wonThisMonth->sum('value'), 2),
        ];
    }

    private function getConversionStats(int $teamId): array
    {
        $wonCount = Opportunity::where('team_id', $teamId)->whereNotNull('won_at')->count();
        $lostCount = Opportunity::where('team_id', $teamId)->whereNotNull('lost_at')->count();
        $total = $wonCount + $lostCount;

        $winRate = $total > 0 ? round($wonCount / $total * 100, 1) : 0;

        $avgDealSize = Opportunity::where('team_id', $teamId)
            ->whereNotNull('won_at')
            ->avg('value') ?? 0;

        $avgDaysToClose = Opportunity::where('team_id', $teamId)
            ->whereNotNull('won_at')
            ->whereNotNull('started_at')
            ->selectRaw('AVG(DATEDIFF(won_at, started_at)) as avg_days')
            ->value('avg_days') ?? 0;

        return [
            'win_rate' => $winRate,
            'avg_deal_size' => round($avgDealSize, 2),
            'avg_days_to_close' => round($avgDaysToClose),
        ];
    }

    private function getIncomingLeads(int $teamId): array
    {
        $trend = Trend::model(Contact::class)
            ->between(start: now()->subMonths(11)->startOfMonth(), end: now()->endOfMonth())
            ->perMonth()
            ->count();

        return [
            'labels' => $trend->map(fn ($item) => $item->date)->toArray(),
            'data' => $trend->map(fn ($item) => $item->aggregate)->toArray(),
        ];
    }

    private function getRevenueOverTime(int $teamId): array
    {
        $trend = Trend::query(
            Opportunity::where('team_id', $teamId)->whereNotNull('won_at')
        )
            ->between(start: now()->subMonths(11)->startOfMonth(), end: now()->endOfMonth())
            ->dateColumn('won_at')
            ->perMonth()
            ->sum('value');

        return [
            'labels' => $trend->map(fn ($item) => $item->date)->toArray(),
            'data' => $trend->map(fn ($item) => $item->aggregate)->toArray(),
        ];
    }

    private function getWonLostOverTime(int $teamId): array
    {
        $wonTrend = Trend::query(
            Opportunity::where('team_id', $teamId)->whereNotNull('won_at')
        )
            ->between(start: now()->subMonths(11)->startOfMonth(), end: now()->endOfMonth())
            ->dateColumn('won_at')
            ->perMonth()
            ->count();

        $lostTrend = Trend::query(
            Opportunity::where('team_id', $teamId)->whereNotNull('lost_at')
        )
            ->between(start: now()->subMonths(11)->startOfMonth(), end: now()->endOfMonth())
            ->dateColumn('lost_at')
            ->perMonth()
            ->count();

        return [
            'labels' => $wonTrend->map(fn ($item) => $item->date)->toArray(),
            'won' => $wonTrend->map(fn ($item) => $item->aggregate)->toArray(),
            'lost' => $lostTrend->map(fn ($item) => $item->aggregate)->toArray(),
        ];
    }

    private function getPipelineByStage(int $teamId): array
    {
        $stages = PipelineStage::where('team_id', $teamId)
            ->where('is_won', false)
            ->where('is_lost', false)
            ->orderBy('position')
            ->withSum(['opportunities' => fn ($q) => $q->whereNull('won_at')->whereNull('lost_at')], 'value')
            ->get();

        return [
            'labels' => $stages->pluck('name')->toArray(),
            'data' => $stages->pluck('opportunities_sum_value')->map(fn ($v) => $v ?? 0)->toArray(),
            'colors' => $stages->pluck('color')->toArray(),
        ];
    }

    private function getLatestOpportunities(int $teamId): array
    {
        return Opportunity::where('team_id', $teamId)
            ->with(['pipelineStage', 'company'])
            ->latest('started_at')
            ->limit(5)
            ->get()
            ->map(fn ($opp) => [
                'id' => $opp->id,
                'name' => $opp->name,
                'value' => $opp->value,
                'started_at' => $opp->started_at?->toDateString(),
                'stage' => $opp->pipelineStage?->name,
                'stage_color' => $opp->pipelineStage?->color,
                'company' => $opp->company?->name,
            ])
            ->toArray();
    }

    private function getTodayTasks(int $teamId): array
    {
        return Task::where('team_id', $teamId)
            ->forUser(Auth::id())
            ->where(function ($q) {
                $q->overdue()->orWhere(fn ($q2) => $q2->today());
            })
            ->with(['opportunity', 'contact', 'company'])
            ->orderBy('due_date')
            ->orderBy('due_time')
            ->orderByDesc('priority')
            ->limit(10)
            ->get()
            ->map(fn ($task) => [
                'id' => $task->id,
                'type' => $task->type->value,
                'type_label' => $task->type->getLabel(),
                'title' => $task->title,
                'due_date' => $task->due_date?->toDateString(),
                'due_time' => $task->due_time?->format('H:i'),
                'priority' => $task->priority,
                'priority_label' => $task->priority_label,
                'is_overdue' => $task->isOverdue(),
                'completed_at' => $task->completed_at?->toIso8601String(),
                'opportunity' => $task->opportunity?->name,
                'contact' => $task->contact?->name,
                'company' => $task->company?->name,
            ])
            ->toArray();
    }
}
