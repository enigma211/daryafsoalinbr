<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('نام و نام خانوادگی')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('ایمیل')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('mobile')
                    ->label('شماره موبایل')
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                TextInput::make('password')
                    ->label('رمز عبور')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),
                Select::make('roles')
                    ->label('نقش‌های کاربر')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => match ($record->name) {
                        'Super Admin' => 'مدیر کل',
                        'Exam Manager' => 'مدیر آزمون',
                        'Field Secretary' => 'دبیر رشته',
                        'Operator' => 'اپراتور',
                        'Question Designer' => 'طراح سوال',
                        'Regulations Reviewer' => 'ناظر مقررات ملی',
                        'Scientific Reviewer' => 'ناظر علمی',
                        default => $record->name,
                    })
                    ->preload(),
                Select::make('categories')
                    ->label('مباحث تخصصی (مخصوص داوران)')
                    ->multiple()
                    ->relationship('categories', 'topic')
                    ->preload(),
            ]);
    }
}
