<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use BackedEnum;
use UnitEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $modelLabel = 'نقش';
    protected static ?string $pluralModelLabel = 'نقش‌ها و دسترسی‌ها';

    public static function getNavigationGroup(): ?string
    {
        return 'مدیریت';
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-shield-check';
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        return $user && $user->hasRole('Super Admin');
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        return $user && $user->hasRole('Super Admin');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('عنوان نقش (به انگلیسی)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('مثال: Inspector یا Editor'),
                
                CheckboxList::make('permissions')
                    ->label('دسترسی‌ها')
                    ->relationship('permissions', 'name')
                    ->columns(2)
                    ->gridDirection('row')
                    ->columnSpanFull()
                    ->searchable()
                    ->bulkToggleable()
                    ->getOptionLabelFromRecordUsing(fn ($record) => match ($record->name) {
                        'create questions' => 'ایجاد سوال',
                        'edit questions' => 'ویرایش سوال',
                        'delete questions' => 'حذف سوال',
                        'view questions' => 'مشاهده سوالات',
                        'publish questions' => 'تایید و انتشار نهایی سوالات',
                        'review scientific questions' => 'داوری علمی سوالات',
                        'review regulation questions' => 'داوری مقررات ملی سوالات',
                        'manage users' => 'مدیریت کاربران',
                        'manage roles' => 'مدیریت نقش‌ها و دسترسی‌ها',
                        'manage settings' => 'تنظیمات سیستم',
                        'view logs' => 'مشاهده لاگ‌های سیستم',
                        default => $record->name,
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('عنوان نقش')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'Super Admin' => 'مدیر کل',
                        'Exam Manager' => 'مدیر آزمون',
                        'Field Secretary' => 'دبیر رشته',
                        'Operator' => 'اپراتور',
                        'Question Designer' => 'طراح سوال',
                        'Regulations Reviewer' => 'ناظر مقررات ملی',
                        'Scientific Reviewer' => 'ناظر علمی',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('تعداد دسترسی‌ها')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i:s') : '-')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRoles::route('/'),
        ];
    }
}
