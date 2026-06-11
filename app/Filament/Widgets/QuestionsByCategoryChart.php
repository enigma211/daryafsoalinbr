<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Question;
use App\Models\Category;

class QuestionsByCategoryChart extends ChartWidget
{
    protected ?string $heading = 'توزیع سوالات بر اساس مباحث (کل بانک)';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $categories = Category::withCount('questions')
            ->whereHas('questions')
            ->get();

        $labels = $categories->pluck('topic')->toArray();
        $data = $categories->pluck('questions_count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'تعداد سوال',
                    'data' => $data,
                    'backgroundColor' => [
                        '#36A2EB',
                        '#FF6384',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40',
                        '#32a852',
                        '#e05a38',
                        '#c738e0',
                        '#38e0d5'
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
