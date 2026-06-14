<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Infolists\Components\RepeatableEntry::make('messages')
                    ->hiddenLabel()
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(3)
                            ->schema([
                                TextEntry::make('ticket.subject')
                                    ->label('موضوع')
                                    ->visible(fn ($record) => $record && $record->ticket && $record->id === $record->ticket->messages->sortBy('id')->first()?->id)
                                    ->formatStateUsing(fn ($state, $record) => $record->ticket->subject ?? '-'),
                                TextEntry::make('user.name')
                                    ->label('فرستنده')
                                    ->weight('bold')
                                    ->color(fn ($record) => $record->user_id === \Illuminate\Support\Facades\Auth::id() ? 'primary' : 'gray'),
                                TextEntry::make('created_at')
                                    ->label('تاریخ')
                                    ->formatStateUsing(fn ($state) => \Morilog\Jalali\Jalalian::forge($state)->format('Y/m/d H:i')),
                            ]),
                        TextEntry::make('message')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->formatStateUsing(fn($state) => nl2br(e($state)))
                            ->html(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
