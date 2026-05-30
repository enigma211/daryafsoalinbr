<?php

namespace App\Filament\Widgets;

use App\Models\Question;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopDesignersChart extends ChartWidget
{
    protected ?string $heading = 'برترین طراحان سوال (تعداد تایید شده)';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = Question::select('users.name', DB::raw('count(questions.id) as total'))
            ->join('users', 'questions.user_id', '=', 'users.id')
            ->where('questions.current_status', 'approved')
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $labels = $data->pluck('name')->toArray();
        $values = $data->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'سوالات ثبت و تایید شده',
                    'data' => $values,
                    'backgroundColor' => [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
