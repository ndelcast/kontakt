<?php

namespace App\Filament\Widgets;

use App\Models\Opportunity;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ConversionRateStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $wonCount = Opportunity::whereNotNull('won_at')->count();
        $lostCount = Opportunity::whereNotNull('lost_at')->count();
        $closedCount = $wonCount + $lostCount;

        $winRate = $closedCount > 0 ? round(($wonCount / $closedCount) * 100, 1) : 0;

        $avgDealSize = Opportunity::whereNotNull('won_at')->avg('value') ?? 0;

        $avgDaysToClose = Opportunity::whereNotNull('won_at')
            ->selectRaw('AVG(DATEDIFF(won_at, created_at)) as avg_days')
            ->value('avg_days') ?? 0;

        // Monthly win rates for sparkline
        $winRateSparkline = [];
        $dealSizeSparkline = [];
        $daysSparkline = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $mWon = Opportunity::whereNotNull('won_at')
                ->whereMonth('won_at', $month->month)
                ->whereYear('won_at', $month->year)
                ->count();
            $mLost = Opportunity::whereNotNull('lost_at')
                ->whereMonth('lost_at', $month->month)
                ->whereYear('lost_at', $month->year)
                ->count();
            $mClosed = $mWon + $mLost;
            $winRateSparkline[] = $mClosed > 0 ? round(($mWon / $mClosed) * 100) : 0;

            $mAvg = Opportunity::whereNotNull('won_at')
                ->whereMonth('won_at', $month->month)
                ->whereYear('won_at', $month->year)
                ->avg('value') ?? 0;
            $dealSizeSparkline[] = (int) $mAvg;

            $mDays = Opportunity::whereNotNull('won_at')
                ->whereMonth('won_at', $month->month)
                ->whereYear('won_at', $month->year)
                ->selectRaw('AVG(DATEDIFF(won_at, created_at)) as avg_days')
                ->value('avg_days') ?? 0;
            $daysSparkline[] = (int) round($mDays);
        }

        // Previous month comparison
        $prevMonth = now()->subMonth();
        $prevWon = Opportunity::whereNotNull('won_at')
            ->whereMonth('won_at', $prevMonth->month)
            ->whereYear('won_at', $prevMonth->year)
            ->count();
        $prevLost = Opportunity::whereNotNull('lost_at')
            ->whereMonth('lost_at', $prevMonth->month)
            ->whereYear('lost_at', $prevMonth->year)
            ->count();
        $prevClosed = $prevWon + $prevLost;
        $prevWinRate = $prevClosed > 0 ? round(($prevWon / $prevClosed) * 100, 1) : 0;
        $winRateDiff = round($winRate - $prevWinRate, 1);

        return [
            Stat::make(__('Win Rate'), $winRate . '%')
                ->description($winRateDiff >= 0 ? '+' . $winRateDiff . ' ' . __('pts vs last month') : $winRateDiff . ' ' . __('pts vs last month'))
                ->descriptionIcon($winRateDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($winRate >= 50 ? 'success' : 'warning')
                ->chart($winRateSparkline),
            Stat::make(__('Avg Deal Size'), number_format($avgDealSize, 2, ',', ' ') . ' €')
                ->description(__('Won opportunities'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info')
                ->chart($dealSizeSparkline),
            Stat::make(__('Avg Days to Close'), round($avgDaysToClose, 0))
                ->description(__('From creation to won'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray')
                ->chart($daysSparkline),
        ];
    }
}
