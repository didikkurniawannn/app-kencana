<?php

namespace App\Filament\Widgets;

use App\Models\DetailBelanja;
use App\Models\Realisasi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Helpers\ActiveYear;
use App\Models\Sp2d;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $activeYear = ActiveYear::get();
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;

        // 1. Total Pagu
        $totalPagu = DetailBelanja::whereHas('rekening.subKegiatan.kegiatan.program', function ($q) use ($activeYear, $tenantId) {
            $q->where('tahun_anggaran', $activeYear);
            if ($tenantId)
                $q->where('instansi_id', $tenantId);
        })->sum('pagu');

        // 2. Total Realisasi (Approved)
        $totalRealisasi = Realisasi::where('status', 'disetujui')
            ->whereYear('tanggal_realisasi', $activeYear)
            ->whereHas('detailBelanja.rekening.subKegiatan.kegiatan.program', function ($q) use ($tenantId) {
                if ($tenantId)
                    $q->where('instansi_id', $tenantId);
            })
            ->sum('jumlah');

        // 3. Sisa Kas Tersedia (Dihitung dari SP2D)
        $totalKas = Sp2d::where('is_active', true)
            ->when($tenantId, fn($q) => $q->where('instansi_id', $tenantId))
            ->sum('sisa_jumlah');

        $sisaAnggaran = $totalPagu - $totalRealisasi;
        $persentase = $totalPagu > 0 ? ($totalRealisasi / $totalPagu) * 100 : 0;

        $pendingCount = Realisasi::where('status', 'submitted')
            ->whereYear('tanggal_realisasi', $activeYear)
            ->whereHas('detailBelanja.rekening.subKegiatan.kegiatan.program', function ($q) use ($tenantId) {
                if ($tenantId)
                    $q->where('instansi_id', $tenantId);
            })
            ->count();

        return [
            Stat::make('Total Pagu Anggaran', 'Rp ' . number_format($totalPagu, 0, ',', '.'))
                ->description('Plafond anggaran ' . $activeYear)
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary')
                ->chart([7, 3, 5, 2, 4, 6, 8]),

            Stat::make('Kas Tersedia', 'Rp ' . number_format($totalKas, 0, ',', '.'))
                ->description('Total dana SP2D aktif')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('success')
                ->chart([2, 4, 6, 1, 3, 5, 7]),

            Stat::make('Realisasi Anggaran', 'Rp ' . number_format($totalRealisasi, 0, ',', '.'))
                ->description(number_format($persentase, 1) . '% Terpakai')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($persentase >= 80 ? 'danger' : ($persentase >= 50 ? 'warning' : 'success')),

            Stat::make('Sisa Anggaran', 'Rp ' . number_format($sisaAnggaran, 0, ',', '.'))
                ->description(number_format(100 - $persentase, 1) . '% Sisa Pagu')
                ->descriptionIcon('heroicon-m-wallet')
                ->color($sisaAnggaran > 0 ? 'info' : 'danger'),

            Stat::make('Pending Approval', $pendingCount)
                ->description('Verifikasi realisasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'gray'),
        ];
    }
}
