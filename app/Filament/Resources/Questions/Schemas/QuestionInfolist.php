<?php

namespace App\Filament\Resources\Questions\Schemas;

use App\Models\Question;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class QuestionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('unique_code')
                    ->label('کد یکتا'),
                TextEntry::make('text')
                    ->label('متن سوال')
                    ->html()
                    ->columnSpanFull(),
                \Filament\Infolists\Components\RepeatableEntry::make('options')
                    ->label('گزینه‌های سوال')
                    ->schema([
                        TextEntry::make('text')
                            ->hiddenLabel()
                            ->html()
                            ->formatStateUsing(function ($state, \App\Models\QuestionOption $record) {
                                return "<div style='display: flex; gap: 1rem; align-items: center;'>
                                    <span style='font-size: 0.9rem; font-weight: bold; background-color: rgba(0,0,0,0.05); padding: 4px 10px; border-radius: 6px; white-space: nowrap;'>گزینه {$record->option_number}</span> 
                                    <div style='flex-grow: 1;'>{$state}</div>
                                </div>";
                            })
                            ->extraAttributes(function (\App\Models\QuestionOption $record) {
                                $isCorrect = $record->question && $record->question->correct_option == $record->option_number;
                                return $isCorrect 
                                    ? ['style' => 'background-color: #dcfce7; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 2px solid #22c55e; margin-bottom: 0.5rem;'] 
                                    : ['style' => 'background-color: #f9fafb; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; margin-bottom: 0.5rem;'];
                            }),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
                TextEntry::make('exact_source')
                    ->label('محل دقیق مرجع')
                    ->placeholder('-'),
                TextEntry::make('difficulty_level')
                    ->label('درجه سختی')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'easy' => 'ساده',
                        'medium' => 'متوسط',
                        'hard' => 'سخت',
                        default => $state,
                    })
                    ->placeholder('-'),
                TextEntry::make('estimated_time')
                    ->label('زمان تخمینی')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('current_status')
                    ->label('وضعیت فعلی')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'پیش‌نویس',
                        'incomplete' => 'ناقص',
                        'awaiting_review' => 'در انتظار بررسی',
                        'published' => 'منتشر شده',
                        default => $state,
                    }),
                TextEntry::make('user_id')
                    ->label('شناسه کاربر')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::forge($state)->format('Y/m/d H:i') : '-')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('تاریخ آخرین ویرایش')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::forge($state)->format('Y/m/d H:i') : '-')
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->label('تاریخ حذف')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::forge($state)->format('Y/m/d H:i') : '-')
                    ->visible(fn (Question $record): bool => $record->trashed()),
            ]);
    }
}
