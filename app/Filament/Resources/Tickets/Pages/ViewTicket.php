<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected string $view = 'filament.resources.tickets.pages.view-ticket';

    public ?array $replyData = [];

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'گفتگو با پشتیبانی';
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);
    }

    public function replyForm(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Textarea::make('message')
                    ->hiddenLabel()
                    ->placeholder('پاسخ خود را اینجا بنویسید...')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->statePath('replyData');
    }

    public function reply(): void
    {
        $data = $this->getSchema('replyForm')->getState();

        $this->record->messages()->create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'message' => $data['message'],
        ]);

        if (filament()->getCurrentPanel()->getId() === 'admin') {
            $this->record->update(['status' => 'answered']);
        } else {
            $this->record->update(['status' => 'open']);
        }

        $this->replyData = ['message' => ''];
        
        \Filament\Notifications\Notification::make()
            ->title('پاسخ ارسال شد')
            ->success()
            ->send();
    }
}
