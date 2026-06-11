<?php

namespace App\Filament\Resources\RecycleBinResource\Pages;

use App\Filament\Resources\RecycleBinResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRecycleBin extends ManageRecords
{
    protected static string $resource = RecycleBinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No top actions needed
        ];
    }
}
