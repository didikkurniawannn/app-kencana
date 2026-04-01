<?php

namespace App\Filament\Pages;

use App\Models\Sp2d;
use App\Models\Realisasi;
use App\Helpers\ActiveYear;
use App\Models\Setting;
use App\Exports\BkuExport;
use Filament\Pages\Page;
use Filament\Facades\Filament;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class BukuBku extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Buku BKU';
    protected static ?string $title = 'Buku Kas Umum (BKU)';
    protected static ?string $slug = 'buku-bku';

    protected static string $view = 'filament.pages.buku-bku';

    // Filter state
    public string $activeTab = 'semua';
    public ?string $filterBulan = null;
    public ?string $filterTriwulan = null;
    public ?string $filterFrom = null;
    public ?string $filterUntil = null;

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getBkuData(): Collection
    {
        $tenant = Filament::getTenant();
        $tenantId = $tenant?->id;
        $activeYear = ActiveYear::get();

        $entries = collect();

        // === UANG MASUK: SP2D entries (semua sumber dana) ===
        $sp2dQuery = Sp2d::where('instansi_id', $tenantId)
            ->whereYear('tanggal_sp2d', $activeYear);

        // Tab filter: Non Pegawai — exclude semua SP2D dengan sumber_dana = 'LS'
        if ($this->activeTab === 'non-pegawai') {
            $sp2dQuery->where('sumber_dana', '!=', 'LS');
        }

        $this->applyDateFilter($sp2dQuery, 'tanggal_sp2d');

        $sp2ds = $sp2dQuery->orderBy('tanggal_sp2d')->get();

        foreach ($sp2ds as $sp2d) {
            $entries->push([
                'tanggal' => $sp2d->tanggal_sp2d,
                'tipe' => 'masuk',
                'uraian' => 'Pencairan SP2D: ' . ($sp2d->nama_sumber_dana ?: $sp2d->nomor_sp2d),
                'nomor_bukti' => $sp2d->nomor_sp2d,
                'sumber_dana' => $sp2d->sumber_dana,
                'uang_masuk' => (float) $sp2d->jumlah_sp2d,
                'uang_keluar' => 0,
                'kegiatan' => $sp2d->nama_sumber_dana ?: $sp2d->sumber_dana,
                'expense_type' => null,
            ]);
        }

        // === UANG KELUAR: Realisasi entries ===
        $realisasiQuery = Realisasi::with([
                'detailBelanja.rekening.subKegiatan.kegiatan',
                'sp2d',
                'expenseType',
            ])
            ->where('instansi_id', $tenantId)
            ->where('status', 'disetujui')
            ->whereHas('detailBelanja.rekening.subKegiatan.kegiatan.program', function ($q) use ($activeYear) {
                $q->where('tahun_anggaran', $activeYear);
            });

        // Tab filter: Non Pegawai — exclude realisasi dengan jenis pengeluaran 'Belanja Pegawai'
        if ($this->activeTab === 'non-pegawai') {
            $pegawaiTypeIds = \App\Models\ExpenseType::where('instansi_id', $tenantId)
                ->where('name', 'Belanja Pegawai')
                ->pluck('id')
                ->toArray();

            if (!empty($pegawaiTypeIds)) {
                $realisasiQuery->whereNotIn('expense_type_id', $pegawaiTypeIds);
            }
        }

        $this->applyDateFilter($realisasiQuery, 'tanggal_realisasi');

        $realisasis = $realisasiQuery->orderBy('tanggal_realisasi')->get();

        foreach ($realisasis as $r) {
            $kegiatan = $r->detailBelanja?->rekening?->subKegiatan?->kegiatan;
            $kegiatanText = $kegiatan
                ? $kegiatan->kode_kegiatan . ' - ' . $kegiatan->nama_kegiatan
                : '-';

            $entries->push([
                'tanggal' => $r->tanggal_realisasi,
                'tipe' => 'keluar',
                'uraian' => $r->detailBelanja?->nama_detail_belanja ?? '-',
                'nomor_bukti' => $r->sp2d?->nomor_sp2d ?? '-',
                'sumber_dana' => $r->sp2d?->sumber_dana ?? '-',
                'uang_masuk' => 0,
                'uang_keluar' => (float) $r->jumlah,
                'kegiatan' => $kegiatanText,
                'expense_type' => $r->expenseType?->name,
            ]);
        }

        // Sort chronologically
        $entries = $entries->sortBy('tanggal')->values();

        // Calculate running saldo
        $saldo = 0;
        $entries = $entries->map(function ($entry) use (&$saldo) {
            $saldo += $entry['uang_masuk'] - $entry['uang_keluar'];
            $entry['saldo'] = $saldo;
            return $entry;
        });

        return $entries;
    }

    protected function applyDateFilter($query, string $dateColumn): void
    {
        if ($this->filterBulan) {
            $query->whereMonth($dateColumn, $this->filterBulan);
        }

        if ($this->filterTriwulan) {
            $months = match ($this->filterTriwulan) {
                '1' => [1, 2, 3],
                '2' => [4, 5, 6],
                '3' => [7, 8, 9],
                '4' => [10, 11, 12],
                default => [],
            };
            if (!empty($months)) {
                $query->where(function ($q) use ($dateColumn, $months) {
                    foreach ($months as $m) {
                        $q->orWhereMonth($dateColumn, $m);
                    }
                });
            }
        }

        if ($this->filterFrom) {
            $query->whereDate($dateColumn, '>=', $this->filterFrom);
        }

        if ($this->filterUntil) {
            $query->whereDate($dateColumn, '<=', $this->filterUntil);
        }
    }

    public function getSummary(): array
    {
        $data = $this->getBkuData();

        return [
            'total_masuk' => $data->sum('uang_masuk'),
            'total_keluar' => $data->sum('uang_keluar'),
            'saldo_akhir' => $data->last()['saldo'] ?? 0,
            'jumlah_transaksi' => $data->count(),
        ];
    }

    public function resetFilters(): void
    {
        $this->filterBulan = null;
        $this->filterTriwulan = null;
        $this->filterFrom = null;
        $this->filterUntil = null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_bku')
                ->label('Export BKU Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $data = $this->getBkuData();
                    $tabLabel = $this->activeTab === 'non-pegawai' ? 'Non Pegawai' : 'Semua';
                    $fileName = 'bku-' . \Illuminate\Support\Str::slug($tabLabel) . '-' . now()->format('Y-m-d') . '.xlsx';
                    return Excel::download(new BkuExport($data, $tabLabel), $fileName);
                }),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi', 'bendahara', 'verifikator', 'pimpinan']) ?? false;
    }
}
