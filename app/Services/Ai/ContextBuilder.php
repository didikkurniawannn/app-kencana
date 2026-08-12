<?php

namespace App\Services\Ai;

use App\Models\Program;
use App\Models\Realisasi;
use App\Models\Sp2d;
use App\Models\SubKegiatan;
use App\Models\ExpenseType;
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

        $cacheKey = "ai_snapshot_v3_{$tenant->id}_{$activeYear}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($tenant, $activeYear) {
            // 1. Metadata Instansi
            $snapshot = [
                'instansi' => $tenant->name ?? $tenant->nama_instansi,
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

            // 2b. Daftar Seluruh Program (Full Hierarchy Knowledge)
            $snapshot['daftar_program'] = $programs->map(fn($p) => [
                'k' => $p->kode_program,
                'n' => $p->nama_program,
                'p' => $p->total_pagu,
                'r' => $p->total_realisasi,
                's' => $p->sisa_pagu
            ])->values()->toArray();

            // 3. Tren Realisasi Bulanan
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

            // 4. SELURUH Rincian Belanja (tanpa filter pagu minimum)
            $allBelanja = \App\Models\DetailBelanja::where('instansi_id', $tenant->id)
                ->whereHas('rekening.subKegiatan.kegiatan.program', fn($q) => $q->where('tahun_anggaran', $activeYear))
                ->with(['rekening.subKegiatan'])
                ->orderByDesc('pagu')
                ->get();

            $snapshot['seluruh_belanja'] = $allBelanja->map(fn($d) => [
                'item' => $d->nama_detail_belanja,
                'pagu' => $d->pagu,
                'real' => $d->realisasi_total ?? 0,
                'sisa' => $d->sisa_pagu ?? $d->pagu,
                'rek_kode' => $d->rekening?->kode_rekening,
                'rek_nama' => $d->rekening?->nama_rekening,
                'sub_keg' => $d->rekening?->subKegiatan?->nama_sub_kegiatan,
            ])->values()->toArray();

            // 5. Ringkasan per Sub Kegiatan
            $snapshot['ringkasan_sub_kegiatan'] = $allBelanja->groupBy(fn($d) => $d->rekening?->subKegiatan?->nama_sub_kegiatan ?? 'Tidak Diketahui')
                ->map(fn($items, $name) => [
                    'sub_kegiatan' => $name,
                    'total_pagu' => $items->sum('pagu'),
                    'total_realisasi' => $items->sum('realisasi_total'),
                    'jumlah_item' => $items->count(),
                ])->sortByDesc('total_pagu')->values()->toArray();

            // 6. Ringkasan per Jenis Belanja (Expense Type) — dari data realisasi
            $snapshot['ringkasan_jenis_belanja'] = Realisasi::where('instansi_id', $tenant->id)
                ->where('status', 'disetujui')
                ->whereHas('detailBelanja.rekening.subKegiatan.kegiatan.program', fn($q) => $q->where('tahun_anggaran', $activeYear))
                ->with('expenseType')
                ->get()
                ->groupBy(fn($r) => $r->expenseType?->name ?? 'Belum Dikategorikan')
                ->map(fn($items, $name) => [
                    'jenis' => $name,
                    'total_realisasi' => $items->sum('jumlah'),
                    'jumlah_transaksi' => $items->count(),
                ])->sortByDesc('total_realisasi')->values()->toArray();

            // 7. Agregasi per Kode Rekening (Buku Besar)
            $snapshot['distribusi_rekening'] = DB::table('rekenings')
                ->join('detail_belanjas', 'rekenings.id', '=', 'detail_belanjas.rekening_id')
                ->leftJoin('realisasis', function ($join) {
                    $join->on('detail_belanjas.id', '=', 'realisasis.detail_belanja_id')
                        ->where('realisasis.status', 'disetujui');
                })
                ->where('rekenings.instansi_id', $tenant->id)
                ->whereExists(function ($query) use ($activeYear) {
                    $query->select(DB::raw(1))
                        ->from('sub_kegiatans')
                        ->join('kegiatans', 'sub_kegiatans.kegiatan_id', '=', 'kegiatans.id')
                        ->join('programs', 'kegiatans.program_id', '=', 'programs.id')
                        ->whereColumn('rekenings.sub_kegiatan_id', 'sub_kegiatans.id')
                        ->where('programs.tahun_anggaran', $activeYear);
                })
                ->select(
                    'rekenings.kode_rekening',
                    'rekenings.nama_rekening',
                    DB::raw('SUM(detail_belanjas.pagu) as total_pagu'),
                    DB::raw('COALESCE(SUM(realisasis.jumlah), 0) as total_realisasi')
                )
                ->groupBy('rekenings.kode_rekening', 'rekenings.nama_rekening')
                ->orderByDesc('total_pagu')
                ->get()
                ->map(fn($item) => [
                    'kode' => $item->kode_rekening,
                    'nama' => $item->nama_rekening,
                    'pagu' => (float) $item->total_pagu,
                    'realisasi' => (float) $item->total_realisasi,
                ])
                ->toArray();

            // 8. Status Kas (SP2D)
            $kasMasuk = (float) Sp2d::where('instansi_id', $tenant->id)->where('status_verifikasi', 'diverifikasi')->sum('jumlah_sp2d');
            $kasSisa = (float) Sp2d::where('instansi_id', $tenant->id)->where('status_verifikasi', 'diverifikasi')->sum('sisa_jumlah');

            $snapshot['kas'] = [
                'total_sp2d' => $kasMasuk,
                'sisa_kas' => $kasSisa,
                'terpakai' => $kasMasuk - $kasSisa,
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
            // Clear all versions of the cache
            Cache::forget("ai_snapshot_v2_{$tenant->id}_{$activeYear}");
            Cache::forget("ai_snapshot_v3_{$tenant->id}_{$activeYear}");
        }
    }
}
