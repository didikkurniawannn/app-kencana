<?php

namespace App\Filament\Resources\Sp2dResource\Pages;

use App\Filament\Resources\Sp2dResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSp2d extends EditRecord
{
    protected static string $resource = Sp2dResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
