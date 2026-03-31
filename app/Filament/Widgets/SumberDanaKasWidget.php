<?php

namespace App\Filament\Widgets;

use App\Models\Sp2d;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SumberDanaKasWidget extends ChartWidget
{
    protected static ?string $heading = 'Sisa Kas per Sumber Dana';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 5;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;

        $data = Sp2d::query()
            ->where('is_active', true)
            ->when($tenantId, fn($q) => $q->where('instansi_id', $tenantId))
            ->select('nama_sumber_dana', DB::raw('SUM(sisa_jumlah) as total'))
            ->groupBy('nama_sumber_dana')
            ->having('total', '>', 0)
            ->get();

        if ($data->isEmpty()) {
            return [
                'datasets' => [[
                    'label' => 'Sisa Kas (Rp)',
                    'data' => [0],
                    'backgroundColor' => ['#e2e8f0'],
                ]],
                'labels' => ['Belum ada dana'],
            ];
        }

        $labels = $data->pluck('nama_sumber_dana')->toArray();
        $values = $data->pluck('total')->toArray();
        
        // Premium colors palette (Jet style)
        $colors = [
            '#3b82f6', # Blue
            '#10b981', # Emerald
            '#f59e0b', # Amber
            '#8b5cf6', # Violet
            '#ec4899', # Pink
            '#06b6d4', # Cyan
            '#f97316', # Orange
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Sisa Kas (Rp)',
                    'data' => $values,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
                    'hoverOffset' => 4
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '70%',
        ];
    }
}
