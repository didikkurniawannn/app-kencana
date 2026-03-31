<?php

namespace App\Filament\Resources\SubKegiatanResource\Pages;

use App\Filament\Resources\SubKegiatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubKegiatans extends ListRecords
{
    protected static string $resource = SubKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
