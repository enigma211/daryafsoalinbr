<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        $topics = [];
        for ($i = 1; $i <= 24; $i++) {
            $topics["مبحث $i"] = "مبحث $i";
        }

        return $schema
            ->components([
                Select::make('topic')
                    ->label('مبحث مقررات ملی')
                    ->options($topics)
                    ->required()
                    ->searchable(),
                TextInput::make('edition')
                    ->label('ویرایش (سال)')
                    ->maxLength(255),
                TextInput::make('chapter')
                    ->label('فصل')
                    ->maxLength(255),
                TextInput::make('clause')
                    ->label('بند')
                    ->maxLength(255),
                TextInput::make('page')
                    ->label('صفحه')
                    ->maxLength(255),
            ]);
    }
}
