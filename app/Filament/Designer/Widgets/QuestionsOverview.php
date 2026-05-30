<?php

namespace App\Filament\Designer\Widgets;

use App\Models\Question;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class QuestionsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $userId = Auth::id();

        $totalQuestions = Question::where('user_id', $userId)->count();
        $publishedQuestions = Question::where('user_id', $userId)->where('current_status', 'published')->count();
        $awaitingReview = Question::where('user_id', $userId)->where('current_status', 'awaiting_review')->count();
        $draftQuestions = Question::where('user_id', $userId)->whereIn('current_status', ['draft', 'incomplete'])->count();

        return [
            Stat::make('کل سوالات ثبت شده', $totalQuestions)
                ->description('تعداد کل سوالات ارسالی شما')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('سوالات تایید شده', $publishedQuestions)
                ->description('سوالاتی که منتشر شده‌اند')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('در انتظار داوری', $awaitingReview)
                ->description('در حال بررسی توسط داوران')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
