<?php

namespace App\Filament\Resources\RealisasiResource\Pages;

use App\Filament\Resources\RealisasiResource;
use App\Exports\RealisasiExport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListRealisasis extends ListRecords
{
    protected static string $resource = RealisasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Input Realisasi Baru'),
            Actions\Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $query = $this->getFilteredTableQuery();
                    return Excel::download(new RealisasiExport($query), 'realisasi_' . date('Y-m-d') . '.xlsx');
                }),
        ];
    }
}
