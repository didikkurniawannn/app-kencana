<?php

namespace App\Filament\Resources\LaporanArsipResource\Pages;

use App\Filament\Resources\LaporanArsipResource;
use Filament\Resources\Pages\ListRecords;

class ListLaporanArsips extends ListRecords
{
    protected static string $resource = LaporanArsipResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
