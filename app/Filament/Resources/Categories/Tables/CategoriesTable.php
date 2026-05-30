<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('topic')
                    ->label('مبحث')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('edition')
                    ->label('ویرایش')
                    ->searchable(),
                TextColumn::make('chapter')
                    ->label('فصل')
                    ->searchable(),
                TextColumn::make('clause')
                    ->label('بند')
                    ->searchable(),
                TextColumn::make('page')
                    ->label('صفحه')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('topic')
                    ->label('فیلتر مبحث')
                    ->options(function () {
                        $topics = [];
                        for ($i = 1; $i <= 24; $i++) {
                            $topics["مبحث $i"] = "مبحث $i";
                        }
                        return $topics;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
