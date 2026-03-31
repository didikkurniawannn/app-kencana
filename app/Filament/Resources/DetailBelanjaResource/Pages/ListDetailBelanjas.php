<?php

namespace App\Filament\Resources\DetailBelanjaResource\Pages;

use App\Filament\Resources\DetailBelanjaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetailBelanjas extends ListRecords
{
    protected static string $resource = DetailBelanjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
