<?php

namespace App\Services\Ai;

use App\Models\Program;
use App\Models\Realisasi;
use App\Models\Sp2d;
use App\Models\SubKegiatan;
use App\Helpers\ActiveYear;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ContextBuilder
{
    /**
     * Get a comprehensive financial summary for the current tenant with caching.
     */
    public function getSnapshot(): array
    {
        $tenant = Filament::getTenant();
        $activeYear = ActiveYear::get();

        if (!$tenant) {
            return ['error' => 'No instansi context found.'];
        }

        $cacheKey = "ai_snapshot_v2_{$tenant->id}_{$activeYear}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($tenant, $activeYear) {
            // 1. Metadata Instansi
            $snapshot = [
                'instansi' => $tenant->nama_instansi,
                'tahun' => $activeYear,
                'current_time' => now()->format('d/m/Y H:i'),
            ];

            // 2. Ringkasan Pagu vs Realisasi (Global)
            $programs = Program::where('instansi_id', $tenant->id)
                ->where('tahun_anggaran', $activeYear)
                ->get();

            $totalPagu = $programs->sum('total_pagu');

            $realisasiQuery = Realisasi::where('instansi_id', $tenant->id)
                ->where('status', 'disetujui')
                ->whereHas('detailBelanja.rekening.subKegiatan.kegiatan.program', fn($q) => $q->where('tahun_anggaran', $activeYear));

            $totalRealisasi = (float) $realisasiQuery->sum('jumlah');

            $snapshot['total'] = [
                'pagu' => $totalPagu,
                'realisasi' => $totalRealisasi,
                'penyerapan' => $totalPagu > 0 ? round(($totalRealisasi / $totalPagu) * 100, 2) . '%' : '0%',
                'sisa' => $totalPagu - $totalRealisasi,
            ];

            // 3. Tren Realisasi Bulanan (Tahun Berjalan)
            $monthlyTrends = Realisasi::where('instansi_id', $tenant->id)
                ->where('status', 'disetujui')
                ->whereYear('tanggal_realisasi', $activeYear)
                ->select(DB::raw('MONTH(tanggal_realisasi) as month'), DB::raw('SUM(jumlah) as total'))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('total', 'month')
                ->toArray();

            $snapshot['tren_bulanan'] = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthName = date('F', mktime(0, 0, 0, $m, 1));
                if (isset($monthlyTrends[$m])) {
                    $snapshot['tren_bulanan'][$monthName] = (float) $monthlyTrends[$m];
                }
            }

            // 4. Top 10 Sub-Kegiatan Low Absorption (Pagu > 0)
            // We'll look for those with high budget but low realization percentage
            $snapshot['low_absorption_sub'] = SubKegiatan::where('instansi_id', $tenant->id)
                ->whereHas('kegiatan.program', fn($q) => $q->where('tahun_anggaran', $activeYear))
                ->get()
                ->map(function ($sk) {
                    $pagu = $sk->total_pagu;
                    $real = $sk->total_realisasi;
                    return [
                        'n' => $sk->nama_sub_kegiatan,
                        'p' => $pagu,
                        'r' => $real,
                        'pct' => $pagu > 0 ? round(($real / $pagu) * 100, 2) : 0
                    ];
                })
                ->filter(fn($item) => $item['p'] > 10000000) // Only relevant for budget > 10jt
                ->sortBy('pct')
                ->take(10)
                ->values()
                ->toArray();

            // 5. Analisa per Kategori Belanja (Expense Type)
            $snapshot['kategori_belanja'] = DB::table('realisasis')
                ->join('expense_types', 'realisasis.expense_type_id', '=', 'expense_types.id')
                ->where('realisasis.instansi_id', $tenant->id)
                ->where('realisasis.status', 'disetujui')
                ->whereYear('realisasis.tanggal_realisasi', $activeYear)
                ->select('expense_types.name', DB::raw('SUM(realisasis.jumlah) as total'))
                ->groupBy('expense_types.name')
                ->get()
                ->mapWithKeys(fn($item) => [$item->name => (float) $item->total])
                ->toArray();

            // 6. Status Dana Kas (SP2D Aktif)
            $kasMasuk = (float) Sp2d::where('instansi_id', $tenant->id)->where('status_verifikasi', 'diverifikasi')->sum('jumlah_sp2d');
            $kasSisa = (float) Sp2d::where('instansi_id', $tenant->id)->where('status_verifikasi', 'diverifikasi')->sum('sisa_jumlah');
            
            $snapshot['kas'] = [
                'total_sp2d' => $kasMasuk,
                'sisa_kas' => $kasSisa,
                'terpakai' => $kasMasuk - $kasSisa,
                'rasio_kas' => $kasMasuk > 0 ? round((($kasMasuk - $kasSisa) / $kasMasuk) * 100, 2) . '%' : '0%',
            ];

            return $snapshot;
        });
    }

    /**
     * Clear the cache to force recalculation.
     */
    public function refresh(): void
    {
        $tenant = Filament::getTenant();
        $activeYear = ActiveYear::get();
        if ($tenant) {
            Cache::forget("ai_snapshot_v2_{$tenant->id}_{$activeYear}");
        }
    }
}
