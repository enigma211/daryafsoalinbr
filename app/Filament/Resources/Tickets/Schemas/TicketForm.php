<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Hidden::make('user_id')
                    ->default(fn() => \Illuminate\Support\Facades\Auth::id()),
                TextInput::make('subject')
                    ->label('موضوع تیکت')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('وضعیت')
                    ->options([
                        'open' => 'باز (در انتظار پاسخ)',
                        'answered' => 'پاسخ داده شده',
                        'closed' => 'بسته شده',
                    ])
                    ->default('open')
                    ->required()
                    ->visible(fn () => filament()->getCurrentPanel()->getId() === 'admin'),
                \Filament\Forms\Components\Textarea::make('message')
                    ->label('متن پیام')
                    ->required()
                    ->rows(5)
                    ->visible(fn ($livewire) => $livewire instanceof \App\Filament\Resources\Tickets\Pages\CreateTicket)
                    ->columnSpanFull(),
            ]);
    }
}
