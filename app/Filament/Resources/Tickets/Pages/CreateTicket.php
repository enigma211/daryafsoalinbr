<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected ?string $messageText = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->messageText = $data['message'] ?? null;
        unset($data['message']);
        $data['user_id'] = \Illuminate\Support\Facades\Auth::id();
        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->messageText) {
            $this->record->messages()->create([
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'message' => $this->messageText,
            ]);
        }
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('ارسال');
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            ->hidden();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
