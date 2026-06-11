<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $translations = [
            'id' => 'شناسه (ID)',
            'unique_code' => 'کد یکتا',
            'title' => 'عنوان',
            'text' => 'متن سوال',
            'type' => 'نوع سوال',
            'correct_option' => 'گزینه صحیح',
            'descriptive_answer' => 'پاسخ تشریحی',
            'exact_source' => 'منبع دقیق',
            'current_status' => 'وضعیت',
            'user_id' => 'کاربر (ID)',
            'category_id' => 'مبحث (ID)',
            'created_at' => 'تاریخ ایجاد',
            'updated_at' => 'تاریخ ویرایش',
            'deleted_at' => 'تاریخ حذف',
            'discipline' => 'رشته',
            'qualification' => 'صلاحیت',
            'reference_year' => 'سال مرجع',
            'chapter' => 'فصل',
            'topic_details' => 'جزئیات مبحث',
            'skill_type' => 'نوع مهارت',
            'other_references' => 'سایر منابع',
            'time_reasoning' => 'دلیل زمان‌بندی',
            'edition' => 'ویرایش',
            'reviewer_notes' => 'یادداشت‌های ناظر',
            'options' => 'گزینه‌ها',
            'name' => 'نام',
            'email' => 'ایمیل',
            'topic' => 'نام مبحث',
        ];

        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('جزئیات فعالیت')
                    ->columnSpanFull()
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('causer.name')
                            ->label('کاربر عامل'),
                        \Filament\Infolists\Components\TextEntry::make('event')
                            ->label('عملیات')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'created' => 'ایجاد',
                                'updated' => 'ویرایش',
                                'deleted' => 'حذف',
                                default => $state,
                            }),
                        \Filament\Infolists\Components\TextEntry::make('description')
                            ->label('توضیحات')
                            ->columnSpanFull()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'created' => 'ایجاد شد',
                                'updated' => 'ویرایش شد',
                                'deleted' => 'حذف شد',
                                default => $state,
                            }),
                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->label('تاریخ')
                            ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i:s') : '-'),
                    ])->columns(2),
                    
                \Filament\Schemas\Components\Section::make('تغییرات داده‌ها')
                    ->columnSpanFull()
                    ->schema([
                        \Filament\Infolists\Components\KeyValueEntry::make('properties.old')
                            ->label('مقادیر قبلی')
                            ->keyLabel('ویژگی')
                            ->valueLabel('مقدار')
                            ->columnSpanFull()
                            ->state(function ($record) use ($translations) {
                                $state = $record->properties['old'] ?? [];
                                if (empty($state)) return [];
                                $stateArray = is_object($state) && method_exists($state, 'toArray') ? $state->toArray() : (array) $state;
                                $translated = [];
                                foreach ($stateArray as $key => $value) {
                                    if (in_array($key, ['difficulty_level', 'estimated_time', 'keywords', 'reference_year', 'chapter', 'topic_details', 'skill_type', 'other_references', 'time_reasoning'])) continue;
                                    
                                    $newKey = $translations[$key] ?? $key;
                                    if (in_array($key, ['created_at', 'updated_at', 'deleted_at']) && !empty($value)) {
                                        try { $value = \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($value))->format('Y/m/d H:i:s'); } catch (\Exception $e) {}
                                    }
                                    $translated[$newKey] = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
                                }
                                return $translated;
                            }),
                        \Filament\Infolists\Components\KeyValueEntry::make('properties.attributes')
                            ->label('مقادیر جدید')
                            ->keyLabel('ویژگی')
                            ->valueLabel('مقدار')
                            ->columnSpanFull()
                            ->state(function ($record) use ($translations) {
                                $state = $record->properties['attributes'] ?? [];
                                if (empty($state)) return [];
                                $stateArray = is_object($state) && method_exists($state, 'toArray') ? $state->toArray() : (array) $state;
                                $translated = [];
                                foreach ($stateArray as $key => $value) {
                                    if (in_array($key, ['difficulty_level', 'estimated_time', 'keywords', 'reference_year', 'chapter', 'topic_details', 'skill_type', 'other_references', 'time_reasoning'])) continue;
                                    
                                    $newKey = $translations[$key] ?? $key;
                                    if (in_array($key, ['created_at', 'updated_at', 'deleted_at']) && !empty($value)) {
                                        try { $value = \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($value))->format('Y/m/d H:i:s'); } catch (\Exception $e) {}
                                    }
                                    $translated[$newKey] = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
                                }
                                return $translated;
                            }),
                    ])->columns(1),
            ])->columns(1);
    }
}
