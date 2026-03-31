<?php

namespace App\Filament\Widgets;

use App\Models\Program;
use App\Models\Realisasi;
use Filament\Widgets\ChartWidget;
use App\Helpers\ActiveYear;

class RealisasiPerProgramChart extends ChartWidget
{
    protected static ?string $heading = 'Realisasi per Program';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 7;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $activeYear = ActiveYear::get();
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;

        $programs = Program::query()
            ->where('tahun_anggaran', $activeYear)
            ->when($tenantId, fn($q) => $q->where('instansi_id', $tenantId))
            ->get();

        $labels = [];
        $paguData = [];
        $realisasiData = [];

        foreach ($programs as $program) {
            $labels[] = substr($program->nama_program, 0, 30) . '...';

            // Hitung Pagu Total
            $totalPagu = 0;
            foreach ($program->kegiatans as $kegiatan) {
                foreach ($kegiatan->subKegiatans as $subKegiatan) {
                    foreach ($subKegiatan->rekenings as $rekening) {
                        $totalPagu += $rekening->detailBelanjas->sum('pagu');
                    }
                }
            }

            // Hitung Realisasi Total
            $totalRealisasi = Realisasi::where('status', 'disetujui')
                ->whereYear('tanggal_realisasi', $activeYear)
                ->whereHas('detailBelanja.rekening.subKegiatan.kegiatan', fn($q) => $q->where('program_id', $program->id))
                ->sum('jumlah');

            $paguData[] = $totalPagu / 1000000; // In millions
            $realisasiData[] = $totalRealisasi / 1000000; // In millions
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pagu (Juta Rp)',
                    'data' => $paguData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                ],
                [
                    'label' => 'Realisasi (Juta Rp)',
                    'data' => $realisasiData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
}
