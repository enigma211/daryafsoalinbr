<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Question;
use Carbon\Carbon;

class QuestionsTrendChart extends ChartWidget
{
    protected ?string $heading = 'روند ثبت سوالات در ماه گذشته';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Generate data for the last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = Question::whereDate('created_at', $date)->count();
            
            $data[] = $count;
            // Get Jalali day/month for the label
            $labels[] = \Morilog\Jalali\Jalalian::fromCarbon($date)->format('m/d');
        }

        return [
            'datasets' => [
                [
                    'label' => 'تعداد سوالات ثبت شده',
                    'data' => $data,
                    'fill' => 'start',
                    'borderColor' => '#9333ea', // Purple
                    'backgroundColor' => 'rgba(147, 51, 234, 0.2)', // Light Purple
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
