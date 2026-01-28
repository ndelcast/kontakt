<?php

namespace App\Filament\Widgets;

use App\Models\PipelineStage;
use Filament\Widgets\ChartWidget;

class PipelineValueByStageChart extends ChartWidget
{
    public function getHeading(): string
    {
        return __('Pipeline Value by Stage');
    }

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $stages = PipelineStage::withSum('opportunities', 'value')
            ->orderBy('position')
            ->where('is_won', false)
            ->where('is_lost', false)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('Value'),
                    'data' => $stages->pluck('opportunities_sum_value')->map(fn ($v) => $v ?? 0)->toArray(),
                    'backgroundColor' => $stages->pluck('color')->toArray(),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $stages->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
