<?php

namespace App\Filament\Widgets;

use App\Models\Question;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('سوالات تایید شده', Question::where('current_status', 'approved')->count())
                ->description('تعداد کل سوالات آماده برای آزمون')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('در صف داوری', Question::whereIn('current_status', ['awaiting_review', 'scientific_review', 'regulations_review'])->count())
                ->description('مجموع سوالات منتظر داوران')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('نیاز به اصلاح', Question::where('current_status', 'needs_revision')->count())
                ->description('ارجاع داده شده به طراحان')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
