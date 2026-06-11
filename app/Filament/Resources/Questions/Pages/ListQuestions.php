<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ExportAction::make()
                ->label('خروجی اکسل / CSV')
                ->icon('heroicon-o-document-arrow-down')
                ->exporter(\App\Filament\Exports\QuestionExporter::class)
                ->color('warning')
                ->visible(fn () => filament()->getCurrentPanel()->getId() !== 'designer'),
            CreateAction::make(),
        ];
    }
}
