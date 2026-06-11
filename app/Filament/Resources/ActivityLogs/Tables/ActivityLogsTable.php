<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ و زمان')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i:s') : '-')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('causer.name')
                    ->label('کاربر عامل')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('event')
                    ->label('عملیات')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created' => 'ایجاد',
                        'updated' => 'ویرایش',
                        'deleted' => 'حذف',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created', 'ایجاد' => 'success',
                        'updated', 'ویرایش' => 'warning',
                        'deleted', 'حذف' => 'danger',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('subject_type')
                    ->label('بخش هدف')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'App\Models\Question' => 'سوال',
                            'App\Models\User' => 'کاربر',
                            'App\Models\Category' => 'مبحث',
                            'App\Models\SystemSetting' => 'تنظیمات سیستم',
                            default => $state,
                        };
                    }),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->label('توضیحات')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created' => 'ایجاد شد',
                        'updated' => 'ویرایش شد',
                        'deleted' => 'حذف شد',
                        default => $state,
                    })
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('event')
                    ->label('نوع عملیات')
                    ->options([
                        'created' => 'ایجاد (Created)',
                        'updated' => 'ویرایش (Updated)',
                        'deleted' => 'حذف (Deleted)',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->bulkActions([
                // Readonly!
            ]);
    }
}
