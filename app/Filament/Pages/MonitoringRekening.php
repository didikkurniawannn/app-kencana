<?php

namespace App\Filament\Pages;

use App\Helpers\ActiveYear;
use App\Models\Rekening;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class MonitoringRekening extends Page implements HasTable
{
    public static function canAccess(): bool
    {
        return auth()->check();
    }
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Monitoring Rekening';
    protected static ?string $navigationGroup = null;
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.monitoring-rekening';
    protected static ?string $title = 'Monitoring Realisasi Per Rekening';

    public function table(Table $table): Table
    {
        $activeYear = ActiveYear::get();
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;

        $realisasiSubquery = \App\Models\Realisasi::where('status', 'disetujui')
            ->select('detail_belanja_id', \Illuminate\Support\Facades\DB::raw('SUM(kuefisien) as sum_kuefisien'), \Illuminate\Support\Facades\DB::raw('SUM(jumlah) as sum_realisasi'))
            ->groupBy('detail_belanja_id');

        // Subquery untuk menghitung kolom virtual dan menyiapkan data mentah
        $baseQuery = \App\Models\Rekening::query()
            ->join('detail_belanjas', 'rekenings.id', '=', 'detail_belanjas.rekening_id')
            ->join('sub_kegiatans', 'rekenings.sub_kegiatan_id', '=', 'sub_kegiatans.id')
            ->leftJoinSub($realisasiSubquery, 'realisasi_agg', 'detail_belanjas.id', '=', 'realisasi_agg.detail_belanja_id')
            ->when($tenantId, fn($q) => $q->where('rekenings.instansi_id', $tenantId))
            ->whereHas('subKegiatan.kegiatan.program', function ($q) use ($activeYear, $tenantId) {
                $q->where('tahun_anggaran', $activeYear);
                if ($tenantId) {
                    $q->where('programs.instansi_id', $tenantId);
                }
            })
            ->select(
                'rekenings.id as original_id',
                'rekenings.kode_rekening as raw_kode',
                'rekenings.nama_rekening as raw_nama',
                'rekenings.sub_kegiatan_id as raw_sub_kegiatan_id',
                'sub_kegiatans.nama_sub_kegiatan as raw_sub_nama',
                'detail_belanjas.nama_detail_belanja as raw_detail_nama',
                'detail_belanjas.pagu',
                'detail_belanjas.kuefisien_murni',
                'realisasi_agg.sum_kuefisien as riil_koef',
                'realisasi_agg.sum_realisasi as riil_rupiah',
                \Illuminate\Support\Facades\DB::raw("
                    CASE 
                        WHEN LOWER(detail_belanjas.nama_detail_belanja) LIKE '%pembulatan%' THEN '9.9.99.99.99.9999' 
                        WHEN LOWER(sub_kegiatans.nama_sub_kegiatan) LIKE '%gaji dan tunjangan%' 
                          OR LOWER(sub_kegiatans.nama_sub_kegiatan) LIKE '%jasa penunjang urusan pemerintahan daerah%'
                          OR LOWER(sub_kegiatans.nama_sub_kegiatan) LIKE '%jasa pelayanan umum kantor%'
                        THEN sub_kegiatans.kode_sub_kegiatan
                        ELSE rekenings.kode_rekening 
                    END as v_kode
                "),
                \Illuminate\Support\Facades\DB::raw("
                    CASE 
                        WHEN LOWER(detail_belanjas.nama_detail_belanja) LIKE '%pembulatan%' THEN 'Rekening Khusus Pembulatan' 
                        WHEN LOWER(sub_kegiatans.nama_sub_kegiatan) LIKE '%gaji dan tunjangan%' 
                          OR LOWER(sub_kegiatans.nama_sub_kegiatan) LIKE '%jasa penunjang urusan pemerintahan daerah%'
                          OR LOWER(sub_kegiatans.nama_sub_kegiatan) LIKE '%jasa pelayanan umum kantor%'
                        THEN sub_kegiatans.nama_sub_kegiatan
                        WHEN rekenings.nama_rekening LIKE '%Auto-created%' THEN sub_kegiatans.nama_sub_kegiatan
                        ELSE rekenings.nama_rekening 
                    END as v_nama
                ")
            );

        return $table
            ->query(
                \App\Models\Rekening::query()
                    ->fromSub($baseQuery, 'v_table')
                    ->select(
                        \Illuminate\Support\Facades\DB::raw('MIN(original_id) as id'),
                        'v_kode as kode_rekening',
                        'v_nama as nama_rekening',
                        \Illuminate\Support\Facades\DB::raw('SUM(pagu) as total_pagu_rupiah'),
                        \Illuminate\Support\Facades\DB::raw('SUM(kuefisien_murni) as total_pagu_kuefisien'),
                        \Illuminate\Support\Facades\DB::raw('SUM(riil_koef) as total_riil_kuefisien'),
                        \Illuminate\Support\Facades\DB::raw('SUM(riil_rupiah) as total_riil_rupiah')
                    )
                    ->groupBy('v_kode', 'v_nama')
            )
            ->columns([
                Tables\Columns\TextColumn::make('kode_rekening')
                    ->label('Kode Rekening')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('nama_rekening')
                    ->label('Nama Jenis Rekening')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('kuefisien')
                    ->label('Koefisien (Vol)')
                    ->getStateUsing(fn ($record): string => 
                        (float) $record->total_riil_kuefisien . ' / ' . (float) $record->total_pagu_kuefisien
                    )
                    ->badge()
                    ->color(function ($record) {
                        $pagu = (float) $record->total_pagu_kuefisien;
                        $realisasi = (float) $record->total_riil_kuefisien;
                        if ($pagu <= 0) return 'gray';
                        $percent = ($realisasi / $pagu) * 100;
                        return $percent >= 100 ? 'success' : ($percent >= 50 ? 'warning' : 'danger');
                    }),

                Tables\Columns\TextColumn::make('total_pagu_rupiah')
                    ->label('Pagu Anggaran (Rp)')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Pagu')->money('IDR', locale: 'id')),

                Tables\Columns\TextColumn::make('total_riil_rupiah')
                    ->label('Realisasi (Rp)')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Riil')->money('IDR', locale: 'id')),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Capaian Dana')
                    ->getStateUsing(function ($record): float {
                        $pagu = (float) $record->total_pagu_rupiah;
                        return $pagu > 0 ? ((float) $record->total_riil_rupiah / $pagu) * 100 : 0;
                    })
                    ->formatStateUsing(fn (float $state): string => number_format($state, 2) . '%')
                    ->badge()
                    ->color(function (float $state) {
                        return $state >= 100 ? 'success' : ($state >= 50 ? 'warning' : 'danger');
                    })
                    ->tooltip('Persentase penyerapan anggaran rupiah atas jenis Rekening ini'),
            ])
            ->filters([
                SelectFilter::make('jenis_belanja')
                    ->label('Jenis Belanja')
                    ->options([
                        'gaji' => 'Gaji & Tunjangan',
                        'perjalanan' => 'Perjalanan Dinas',
                        'makan_minum' => 'Makan dan Minum',
                        'pemeliharaan' => 'Pemeliharaan',
                        'tagihan' => 'Tagihan (Listrik/Air/Langganan)',
                        'alat_bahan' => 'Alat/Bahan',
                        'alat' => 'Pembelian Alat/Modal (Aset)',
                        'lainnya' => 'Lainnya',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) return $query;

                        return $query->where(function ($q) use ($data) {
                            switch ($data['value']) {
                                case 'gaji':
                                    $q->where('raw_kode', 'LIKE', '5.1.01%')
                                      ->orWhere('raw_sub_nama', 'LIKE', '%gaji dan tunjangan%');
                                    break;
                                case 'perjalanan':
                                    $q->where('raw_kode', 'LIKE', '5.1.02.04%')
                                      ->orWhere('raw_nama', 'LIKE', '%perjalanan dinas%');
                                    break;
                                case 'makan_minum':
                                    $q->where('raw_nama', 'LIKE', '%makan%')
                                      ->orWhere('raw_nama', 'LIKE', '%minum%')
                                      ->orWhere('raw_detail_nama', 'LIKE', '%makan%')
                                      ->orWhere('raw_detail_nama', 'LIKE', '%minum%');
                                    break;
                                case 'pemeliharaan':
                                    $q->where('raw_kode', 'LIKE', '5.1.02.03%')
                                      ->orWhere('raw_nama', 'LIKE', '%pemeliharaan%');
                                    break;
                                case 'tagihan':
                                    $q->where(function($sub) {
                                        $sub->where('raw_nama', 'LIKE', '%listrik%')
                                            ->orWhere('raw_nama', 'LIKE', '%air%')
                                            ->orWhere('raw_nama', 'LIKE', '%langganan%')
                                            ->orWhere('raw_detail_nama', 'LIKE', '%listrik%')
                                            ->orWhere('raw_detail_nama', 'LIKE', '%air%')
                                            ->orWhere('raw_detail_nama', 'LIKE', '%langganan%');
                                    });
                                    break;
                                case 'alat_bahan':
                                    $q->where(function($sub) {
                                        $sub->where('raw_kode', 'LIKE', '5.1%')
                                            ->where(function($inner) {
                                                $inner->where('raw_nama', 'LIKE', '%alat%')
                                                      ->orWhere('raw_nama', 'LIKE', '%bahan%')
                                                      ->orWhere('raw_detail_nama', 'LIKE', '%alat%')
                                                      ->orWhere('raw_detail_nama', 'LIKE', '%bahan%');
                                            });
                                    });
                                    break;
                                case 'alat':
                                    $q->where('raw_kode', 'LIKE', '5.2%')
                                      ->orWhere('raw_nama', 'LIKE', '%mesin%');
                                    break;
                                case 'lainnya':
                                    $q->where('raw_kode', 'NOT LIKE', '5.1.01%') // Bukan Gaji
                                      ->where('raw_kode', 'NOT LIKE', '5.1.02.04%') // Bukan Perjalanan
                                      ->where('raw_kode', 'NOT LIKE', '5.1.02.03%') // Bukan Pemeliharaan
                                      ->where('raw_kode', 'NOT LIKE', '5.2%') // Bukan Aset Modal
                                      ->where('raw_sub_nama', 'NOT LIKE', '%gaji dan tunjangan%')
                                      ->where('raw_nama', 'NOT LIKE', '%makan%')
                                      ->where('raw_nama', 'NOT LIKE', '%minum%')
                                      ->where('raw_nama', 'NOT LIKE', '%alat%')
                                      ->where('raw_nama', 'NOT LIKE', '%bahan%')
                                      ->where('raw_nama', 'NOT LIKE', '%listrik%')
                                      ->where('raw_nama', 'NOT LIKE', '%air%')
                                      ->where('raw_nama', 'NOT LIKE', '%langganan%')
                                      ->where('raw_detail_nama', 'NOT LIKE', '%listrik%')
                                      ->where('raw_detail_nama', 'NOT LIKE', '%air%')
                                      ->where('raw_detail_nama', 'NOT LIKE', '%langganan%');
                                    break;
                            }
                        });
                    }),

                SelectFilter::make('subKegiatan')
                    ->label('Filter Sub Kegiatan (Asal)')
                    // We must custom the filter query because group by alters the normal Eloquent constraints
                    // `whereHas` works perfectly BEFORE the `groupBy` executes, so we can use a basic filter!
                    ->options(function () use ($activeYear, $tenantId) {
                        return \App\Models\SubKegiatan::whereHas('kegiatan.program', function($q) use ($activeYear, $tenantId) {
                                $q->where('tahun_anggaran', $activeYear)
                                  ->when($tenantId, fn($query) => $query->where('programs.instansi_id', $tenantId));
                            })
                            ->pluck('nama_sub_kegiatan', 'id');
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['value'])) {
                            $query->where('raw_sub_kegiatan_id', $data['value']);
                        }
                        return $query;
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('detail_kegiatan')
                    ->label('Rincian Belanja (Asal)')
                    ->icon('heroicon-o-bars-3-bottom-left')
                    ->button()
                    ->color('info')
                    ->modalSubmitAction(false) // hilangkan tombol submit
                    ->modalCancelAction(fn ($action) => $action->label('Tutup Detail'))
                    ->modalHeading(fn ($record) => 'Daftar Rincian Belanja asal Rekening: ' . $record->nama_rekening)
                    ->modalContent(function ($record) use ($activeYear, $tenantId) {
                        $detailsQuery = \App\Models\DetailBelanja::with('rekening.subKegiatan')
                            ->whereHas('rekening.subKegiatan.kegiatan.program', function ($q) use ($activeYear, $tenantId) {
                                $q->where('tahun_anggaran', $activeYear)
                                  ->when($tenantId, fn($query) => $query->where('programs.instansi_id', $tenantId));
                            });

                        // Jika ini adalah baris virtual "Pembulatan Anggaran"
                        if ($record->kode_rekening === '9.9.99.99.99.9999') {
                            $detailsQuery->whereRaw("LOWER(nama_detail_belanja) LIKE '%pembulatan%'");
                            
                        // Jika ini adalah baris virtual yang tergabung berdasarkan nama Sub Kegiatan Gaji, Jasa Penunjang, atau Pelayanan Umum Kantor
                        } elseif (str_contains(strtolower($record->nama_rekening), 'gaji dan tunjangan') 
                               || str_contains(strtolower($record->nama_rekening), 'jasa penunjang urusan pemerintahan daerah')
                               || str_contains(strtolower($record->nama_rekening), 'jasa pelayanan umum kantor')
                        ) {
                            // Ambil seluruh detail belanja yang ada di dalam Sub Kegiatan tersebut
                            $detailsQuery->whereHas('rekening.subKegiatan', function ($q) use ($record) {
                                $q->where('kode_sub_kegiatan', $record->kode_rekening);
                            })->whereRaw("LOWER(nama_detail_belanja) NOT LIKE '%pembulatan%'");
                            
                        } else {
                            // Untuk rekening biasa, tarik data rekening terkait tapi singkirkan/exclude item pembulatan
                            $detailsQuery->whereHas('rekening', function ($q) use ($record, $tenantId) {
                                $q->where('kode_rekening', $record->kode_rekening)
                                  ->when($tenantId, fn($sq) => $sq->where('rekenings.instansi_id', $tenantId));
                            })->whereRaw("LOWER(nama_detail_belanja) NOT LIKE '%pembulatan%'");
                        }

                        return view('filament.pages.rekening-detail-modal', [
                            'details' => $detailsQuery->get(),
                        ]);
                    }),
            ])
            ->defaultSort('id', 'desc');
    }
}
