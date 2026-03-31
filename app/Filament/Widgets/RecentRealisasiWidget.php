<?php

namespace App\Filament\Widgets;

use App\Models\Realisasi;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Helpers\ActiveYear;

class RecentRealisasiWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Aktivitas Realisasi Terakhir';

    public function table(Table $table): Table
    {
        $activeYear = ActiveYear::get();
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;

        return $table
            ->query(function () use ($tenantId, $activeYear) {
                return Realisasi::query()
                    ->when($tenantId, fn($q) => $q->where('instansi_id', $tenantId))
                    ->whereYear('tanggal_realisasi', $activeYear)
                    ->latest('tanggal_realisasi')
                    ->limit(10);
            })
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_realisasi')
                    ->date('d/m/Y')
                    ->label('Tanggal'),
                Tables\Columns\TextColumn::make('sp2d.nomor_sp2d')
                    ->label('No. SP2D')
                    ->searchable(),
                Tables\Columns\TextColumn::make('detailBelanja.nama_detail_belanja')
                    ->label('Kegiatan/Belanja')
                    ->limit(50),
                Tables\Columns\TextColumn::make('jumlah')
                    ->money('IDR')
                    ->label('Jumlah'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'warning',
                        'disetujui' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->label('Status'),
            ])
            ->paginated(false);
    }
}
