<?php

namespace App\Filament\Widgets;

use App\Models\Realisasi;
use Filament\Widgets\ChartWidget;
use App\Helpers\ActiveYear;
use Illuminate\Support\Facades\DB;

class RealisasiChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Realisasi per Bulan';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $activeYear = ActiveYear::get();
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;

        $data = Realisasi::query()
            ->where('status', 'disetujui')
            ->whereYear('tanggal_realisasi', $activeYear)
            ->whereHas('detailBelanja.rekening.subKegiatan.kegiatan.program', function ($q) use ($tenantId) {
                if ($tenantId)
                    $q->where('instansi_id', $tenantId);
            })
            ->select(
                DB::raw('MONTH(tanggal_realisasi) as month'),
                DB::raw('SUM(jumlah) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $values = array_fill(0, 12, 0);

        foreach ($data as $item) {
            $values[$item->month - 1] = $item->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tren Realisasi (Rp)',
                    'data' => $values,
                    'backgroundColor' => 'rgba(20, 121, 123, 0.15)',
                    'borderColor' => '#14797b',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + value.toLocaleString("id-ID"); }',
                    ],
                ],
            ],
        ];
    }
}
