<?php

namespace App\Filament\Widgets;

use App\Models\Opportunity;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PipelineOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $team = Filament::getTenant();
        $teamId = $team?->id;

        $baseQuery = fn () => Opportunity::when($teamId, fn ($q) => $q->where('opportunities.team_id', $teamId));

        $totalPipelineValue = $baseQuery()
            ->whereHas('pipelineStage', fn ($q) => $q->where('is_won', false)->where('is_lost', false))
            ->sum('value');

        $lastMonthPipelineValue = $baseQuery()
            ->whereHas('pipelineStage', fn ($q) => $q->where('is_won', false)->where('is_lost', false))
            ->where('started_at', '<', now()->startOfMonth())
            ->sum('value');

        $weightedValue = $baseQuery()
            ->whereHas('pipelineStage', fn ($q) => $q->where('is_won', false)->where('is_lost', false))
            ->join('pipeline_stages', 'opportunities.pipeline_stage_id', '=', 'pipeline_stages.id')
            ->selectRaw('SUM(opportunities.value * pipeline_stages.probability / 100) as weighted')
            ->value('weighted') ?? 0;

        $wonThisMonth = $baseQuery()
            ->whereNotNull('won_at')
            ->whereMonth('won_at', now()->month)
            ->whereYear('won_at', now()->year);

        $wonLastMonth = $baseQuery()
            ->whereNotNull('won_at')
            ->whereMonth('won_at', now()->subMonth()->month)
            ->whereYear('won_at', now()->subMonth()->year);

        $wonThisMonthCount = $wonThisMonth->count();
        $wonThisMonthValue = $wonThisMonth->sum('value');
        $wonLastMonthCount = $wonLastMonth->count();

        // Generate sparkline data (last 6 months of pipeline value)
        $pipelineSparkline = [];
        $wonSparkline = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $pipelineSparkline[] = (int) $baseQuery()
                ->where('started_at', '<=', $month->endOfMonth())
                ->whereHas('pipelineStage', fn ($q) => $q->where('is_won', false)->where('is_lost', false))
                ->sum('value');
            $wonSparkline[] = (int) $baseQuery()
                ->whereNotNull('won_at')
                ->whereMonth('won_at', $month->month)
                ->whereYear('won_at', $month->year)
                ->count();
        }

        $pipelineTrend = $lastMonthPipelineValue > 0
            ? round((($totalPipelineValue - $lastMonthPipelineValue) / $lastMonthPipelineValue) * 100, 1)
            : 0;

        $wonTrend = $wonLastMonthCount > 0
            ? round((($wonThisMonthCount - $wonLastMonthCount) / $wonLastMonthCount) * 100, 1)
            : 0;

        return [
            Stat::make(__('Total Pipeline Value'), number_format($totalPipelineValue, 2, ',', ' ') . ' €')
                ->description($pipelineTrend >= 0 ? $pipelineTrend . '% ' . __('increase') : abs($pipelineTrend) . '% ' . __('decrease'))
                ->descriptionIcon($pipelineTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($pipelineTrend >= 0 ? 'success' : 'danger')
                ->chart($pipelineSparkline),
            Stat::make(__('Weighted Value'), number_format($weightedValue, 2, ',', ' ') . ' €')
                ->description(__('Probability-adjusted'))
                ->descriptionIcon('heroicon-m-scale')
                ->color('info')
                ->chart($pipelineSparkline),
            Stat::make(__('Won This Month'), $wonThisMonthCount)
                ->description($wonTrend >= 0 ? number_format($wonThisMonthValue, 2, ',', ' ') . ' € (+' . $wonTrend . '%)' : number_format($wonThisMonthValue, 2, ',', ' ') . ' € (' . $wonTrend . '%)')
                ->descriptionIcon($wonTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($wonTrend >= 0 ? 'success' : 'warning')
                ->chart($wonSparkline),
        ];
    }
}
