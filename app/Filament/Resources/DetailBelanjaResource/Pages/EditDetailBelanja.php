<?php

namespace App\Filament\Resources\DetailBelanjaResource\Pages;

use App\Filament\Resources\DetailBelanjaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDetailBelanja extends EditRecord
{
    protected static string $resource = DetailBelanjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
