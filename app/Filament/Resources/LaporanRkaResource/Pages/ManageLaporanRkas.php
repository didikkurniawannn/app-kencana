<?php

namespace App\Filament\Resources\LaporanRkaResource\Pages;

use App\Filament\Resources\LaporanRkaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLaporanRkas extends ManageRecords
{
    protected static string $resource = LaporanRkaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
