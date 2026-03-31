<?php

namespace App\Filament\Resources\Sp2dResource\Pages;

use App\Filament\Resources\Sp2dResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSp2ds extends ListRecords
{
    protected static string $resource = Sp2dResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
