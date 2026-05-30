<?php

namespace App\Filament\Resources\Questions\RelationManagers;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';
    protected static ?string $title = 'تاریخچه تغییرات سوال';
    protected static \BackedEnum|string|null $icon = 'heroicon-o-clock';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Activity logs should be read-only, no form needed
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('causer.name')
                    ->label('کاربر انجام دهنده')
                    ->placeholder('سیستم'),
                TextEntry::make('event')
                    ->label('نوع رویداد')
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'created' => 'ایجاد سوال',
                        'updated' => 'ویرایش سوال',
                        'deleted' => 'حذف سوال',
                        default => $state,
                    }),
                TextEntry::make('description')
                    ->label('شرح تغییر')
                    ->columnSpanFull(),
                TextEntry::make('properties')
                    ->label('جزئیات کامل تغییرات (JSON)')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label('زمان ثبت رویداد')
                    ->dateTime('Y/m/d H:i:s'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('causer.name')
                    ->label('کاربر انجام دهنده')
                    ->searchable()
                    ->sortable()
                    ->placeholder('سیستم'),
                TextColumn::make('event')
                    ->label('نوع عملیات')
                    ->badge()
                    ->colors([
                        'success' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                    ])
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'created' => 'ایجاد سوال',
                        'updated' => 'ویرایش سوال',
                        'deleted' => 'حذف سوال',
                        default => $state,
                    }),
                TextColumn::make('description')
                    ->label('توضیحات'),
                TextColumn::make('created_at')
                    ->label('زمان ثبت')
                    ->dateTime('Y/m/d H:i:s')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Empty - Read only
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
            ])
            ->toolbarActions([
                // Empty
            ]);
    }
}
