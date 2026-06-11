<?php

namespace App\Filament\Exports;

use App\Models\Question;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class QuestionExporter extends Exporter
{
    protected static ?string $model = Question::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('unique_code')->label('کد یکتا'),
            ExportColumn::make('discipline')
                ->label('رشته')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'civil' => 'عمران',
                    'architecture' => 'معماری',
                    'electrical' => 'تاسیسات برقی',
                    'mechanical' => 'تاسیسات مکانیکی',
                    'surveying' => 'نقشه‌برداری',
                    'traffic' => 'ترافیک',
                    'urbanism' => 'شهرسازی',
                    default => $state ?? '',
                }),
            ExportColumn::make('qualification')
                ->label('صلاحیت / گرایش')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'design' => 'طراحی / محاسبات',
                    'supervision' => 'نظارت',
                    'execution' => 'اجرا',
                    default => $state ?? '',
                }),
            ExportColumn::make('category.topic')->label('مبحث مقررات ملی'),
            ExportColumn::make('reference_year')->label('ویرایش مرجع'),
            ExportColumn::make('chapter')->label('فصل مرجع'),
            ExportColumn::make('topic_details')->label('موضوع دقیق'),
            ExportColumn::make('skill_type')
                ->label('نوع مهارت')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'analysis' => 'تحلیل',
                    'calculation' => 'محاسبه',
                    'regulation_recognition' => 'تشخیص ضابطه',
                    'combined' => 'ترکیبی',
                    default => $state ?? '',
                }),
            ExportColumn::make('type')
                ->label('نوع سوال')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'multiple_choice' => 'چهار گزینه‌ای',
                    'descriptive' => 'تشریحی',
                    default => $state,
                }),
            ExportColumn::make('difficulty_level')
                ->label('درجه سختی')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'easy' => 'آسان',
                    'medium' => 'متوسط',
                    'hard' => 'سخت',
                    default => 'نامشخص',
                }),
            ExportColumn::make('text')->label('متن سوال'),
            ExportColumn::make('options')
                ->label('گزینه‌ها')
                ->state(function (Question $record): string {
                    if ($record->type !== 'multiple_choice') return '-';
                    return $record->options->map(fn($o) => "{$o->option_number}) {$o->text}")->implode(' | ');
                }),
            ExportColumn::make('correct_option')->label('گزینه صحیح (شماره)'),
            ExportColumn::make('descriptive_answer')->label('پاسخ تشریحی'),
            ExportColumn::make('exact_source')->label('منبع دقیق'),
            ExportColumn::make('other_references')->label('سایر منابع'),
            ExportColumn::make('estimated_time')->label('زمان حل (دقیقه)'),
            ExportColumn::make('time_reasoning')->label('دلیل زمان'),
            ExportColumn::make('keywords')->label('کلمات کلیدی'),
            ExportColumn::make('current_status')
                ->label('وضعیت فعلی')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'approved' => 'تایید شده',
                    default => $state,
                }),
            ExportColumn::make('designer.name')->label('نام طراح'),
            ExportColumn::make('created_at')
                ->label('تاریخ ثبت')
                ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d') : ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'خروجی اکسل با موفقیت آماده شد. تعداد ' . Number::format($export->successful_rows) . ' رکورد با موفقیت دریافت شد.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' تعداد ' . Number::format($failedRowsCount) . ' رکورد با خطا مواجه شد.';
        }

        return $body;
    }
}
