<?php

namespace App\Filament\Widgets;

use App\Models\Opportunity;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class WonLostOverTimeChart extends ChartWidget
{
    public function getHeading(): string
    {
        return __('Won vs Lost (Last 12 Months)');
    }

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $team = Filament::getTenant();

        $won = Trend::query(
            Opportunity::whereNotNull('won_at')
                ->when($team, fn ($q) => $q->where('team_id', $team->id))
        )
            ->dateColumn('won_at')
            ->between(
                start: now()->subMonths(11)->startOfMonth(),
                end: now()->endOfMonth(),
            )
            ->perMonth()
            ->count();

        $lost = Trend::query(
            Opportunity::whereNotNull('lost_at')
                ->when($team, fn ($q) => $q->where('team_id', $team->id))
        )
            ->dateColumn('lost_at')
            ->between(
                start: now()->subMonths(11)->startOfMonth(),
                end: now()->endOfMonth(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => __('Won'),
                    'data' => $won->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                    'backgroundColor' => '#22c55e',
                    'borderRadius' => 4,
                ],
                [
                    'label' => __('Lost'),
                    'data' => $lost->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                    'backgroundColor' => '#ef4444',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $won->map(fn (TrendValue $value) => \Carbon\Carbon::parse($value->date)->format('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
