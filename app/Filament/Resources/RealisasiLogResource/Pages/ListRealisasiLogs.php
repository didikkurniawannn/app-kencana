<?php

namespace App\Filament\Resources\RealisasiLogResource\Pages;

use App\Filament\Resources\RealisasiLogResource;
use Filament\Resources\Pages\ListRecords;

class ListRealisasiLogs extends ListRecords
{
    protected static string $resource = RealisasiLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
