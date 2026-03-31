<?php

namespace App\Services;

use App\Models\Sp2d;
use App\Models\SubKegiatan;
use App\Models\Realisasi;
use Carbon\Carbon;

class MonitoringService
{
    /**
     * Get specific targets from PMK 62/2023 (IKPA equivalent standard)
     */
    public function getTargetPercentage(): float
    {
        $month = now()->month;

        // Semester 1 (Jan-Jun) = 50%, Semester 2 (Jul-Dec) = 100%
        if ($month <= 6) {
            return 50.0;
        }
        
        return 100.0;
    }

    /**
     * Get GU Budget Status
     */
    public function getGuBudgetStatus(int $tahun): array
    {
        // Get the latest GU SP2D for the specific year
        // Note: SP2D should ideally also be filtered or have a year. 
        // For now, we'll filters based on the date of SP2D matching the selected year.
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;

        $latestGu = Sp2d::where('sumber_dana', 'LIKE', 'GU%')
            ->whereYear('tanggal_sp2d', $tahun)
            ->when($tenantId, fn($q) => $q->where('instansi_id', $tenantId))
            ->orderBy('tanggal_sp2d', 'desc')
            ->first();

        if (!$latestGu) {
            return [
                'pagu' => 0.0,
                'realized' => 0.0,
                'remaining' => 0.0,
                'percentage' => 0.0,
                'nomor' => 'Belum Ada',
            ];
        }

        $realized = (float) ($latestGu->jumlah_sp2d - $latestGu->sisa_jumlah);

        return [
            'pagu' => (float) $latestGu->jumlah_sp2d,
            'realized' => $realized,
            'remaining' => (float) $latestGu->sisa_jumlah,
            'percentage' => $latestGu->jumlah_sp2d > 0 ? ($realized / $latestGu->jumlah_sp2d) * 100 : 0,
            'nomor' => $latestGu->nomor_sp2d,
        ];
    }

    /**
     * Get Activities requiring attention (Recommendations)
     * Criteria: High remaining budget but realization below current target
     */
    public function getRecommendedActivities(int $tahun, int $limit = 5): \Illuminate\Support\Collection
    {
        $target = $this->getTargetPercentage();

        $tenantId = \Filament\Facades\Filament::getTenant()?->id;

        return SubKegiatan::query()
            ->whereHas('kegiatan.program', function ($q) use ($tahun, $tenantId) {
                $q->where('tahun_anggaran', $tahun);
                if ($tenantId) {
                    $q->where('instansi_id', $tenantId);
                }
            })
            ->get()
            ->map(function ($subKegiatan) use ($target) {
                $pagu = $subKegiatan->total_pagu;
                $realisasi = $subKegiatan->total_realisasi;
                $percentage = $pagu > 0 ? ($realisasi / $pagu) * 100 : 0;
                $remaining = $pagu - $realisasi;

                return [
                    'id' => $subKegiatan->id,
                    'nama' => $subKegiatan->nama_sub_kegiatan,
                    'kode' => $subKegiatan->kode_sub_kegiatan,
                    'pagu' => $pagu,
                    'realisasi' => $realisasi,
                    'percentage' => $percentage,
                    'remaining' => $remaining,
                    'is_below_target' => $percentage < $target,
                    'gap' => $target - $percentage,
                ];
            })
            ->filter(fn($item) => $item['is_below_target'] && $item['remaining'] > 0)
            ->sortByDesc('remaining')
            ->take($limit)
            ->values();
    }
}
