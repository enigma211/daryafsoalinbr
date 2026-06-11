<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecycleBinResource\Pages;
use App\Models\Question;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class RecycleBinResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-trash';
    
    protected static ?string $navigationLabel = 'سطل زباله';
    
    protected static ?string $modelLabel = 'سوال حذف شده';
    
    protected static ?string $pluralModelLabel = 'سطل زباله (سوالات حذف شده)';
    
    protected static \UnitEnum|string|null $navigationGroup = 'مدیریت';
    
    protected static ?int $navigationSort = 100;

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user && $user->hasRole('Super Admin');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->onlyTrashed();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unique_code')
                    ->label('کد یکتا')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('designer.name')
                    ->label('طراح')
                    ->searchable(),
                Tables\Columns\TextColumn::make('text')
                    ->label('متن سوال')
                    ->formatStateUsing(fn ($state) => \Illuminate\Support\Str::limit(strip_tags($state), 60))
                    ->searchable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('تاریخ حذف')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\RestoreAction::make()
                    ->label('بازگردانی')
                    ->color('success'),
                \Filament\Actions\ForceDeleteAction::make()
                    ->label('حذف برای همیشه')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('حذف دائمی سوال')
                    ->modalDescription('آیا مطمئن هستید؟ این عمل غیرقابل بازگشت است و سوال برای همیشه از دیتابیس حذف خواهد شد.'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\RestoreBulkAction::make()
                        ->label('بازگردانی دسته‌جمعی'),
                    \Filament\Actions\ForceDeleteBulkAction::make()
                        ->label('حذف دائمی دسته‌جمعی'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRecycleBin::route('/'),
        ];
    }
}
