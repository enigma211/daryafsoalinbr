<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Question;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class QuestionsPerTopicChart extends ChartWidget
{
    protected ?string $heading = 'تعداد سوالات تایید شده در هر مبحث';
    
    // Sort order
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Get approved questions grouped by category topic
        $data = Question::select('categories.topic', DB::raw('count(questions.id) as total'))
            ->join('categories', 'questions.category_id', '=', 'categories.id')
            ->where('questions.current_status', 'approved')
            ->groupBy('categories.topic')
            ->get();

        $labels = $data->pluck('topic')->toArray();
        $values = $data->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'سوالات تایید شده',
                    'data' => $values,
                    'backgroundColor' => '#36A2EB',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
