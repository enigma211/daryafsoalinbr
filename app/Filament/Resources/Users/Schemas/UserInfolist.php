<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('نام و نام خانوادگی'),
                TextEntry::make('email')
                    ->label('ایمیل'),
                TextEntry::make('mobile')
                    ->label('موبایل')
                    ->placeholder('-'),
                TextEntry::make('roles.name')
                    ->label('نقش‌ها')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'Super Admin' => 'مدیر کل',
                        'Exam Manager' => 'مدیر آزمون',
                        'Field Secretary' => 'دبیر رشته',
                        'Operator' => 'اپراتور',
                        'Question Designer' => 'طراح سوال',
                        'Regulations Reviewer' => 'ناظر مقررات ملی',
                        'Scientific Reviewer' => 'ناظر علمی',
                        default => $state,
                    }),
                TextEntry::make('created_at')
                    ->label('تاریخ عضویت')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i:s') : '-'),
                TextEntry::make('updated_at')
                    ->label('تاریخ ویرایش')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i:s') : '-'),
            ])->columns(2);
    }
}
