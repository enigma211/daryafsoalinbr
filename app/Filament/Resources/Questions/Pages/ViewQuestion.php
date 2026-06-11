<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQuestion extends ViewRecord
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('print')
                ->label('چاپ')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('print.question', $this->record))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
