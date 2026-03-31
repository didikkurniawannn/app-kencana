<?php

namespace App\Filament\Pages;

use App\Services\MonitoringService;
use Filament\Pages\Page;
use App\Helpers\ActiveYear;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Models\Rekening;
use App\Models\DetailBelanja;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class Monitoring extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()->can('page_Monitoring');
    }
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Monitoring';
    protected static ?string $navigationGroup = null;
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.monitoring';
    protected static ?string $title = 'Monitoring Kinerja Kas & Anggaran';

    public float $targetPercentage = 0;
    
    // Cash Flow Variables
    public float $totalUangMasuk = 0;
    public float $totalUangKeluar = 0;
    public float $sisaKas = 0;

    // Search Property
    public $searchQuery = '';
    
    public float $totalPaguKeseluruhan = 0;
    public float $overallPercentage = 0;

    public function mount(MonitoringService $service)
    {
        $activeYear = ActiveYear::get();
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;

        $this->targetPercentage = $service->getTargetPercentage();

        $this->totalUangMasuk = \App\Models\Sp2d::where('is_active', true)
            ->whereYear('tanggal_sp2d', $activeYear)
            ->when($tenantId, fn($q) => $q->where('instansi_id', $tenantId))
            ->sum('jumlah_sp2d');

        $this->totalUangKeluar = \App\Models\Realisasi::where('status', 'disetujui')
            ->whereYear('tanggal_realisasi', $activeYear)
            ->whereHas('detailBelanja.rekening.subKegiatan.kegiatan.program', function ($q) use ($tenantId) {
                if ($tenantId)
                    $q->where('instansi_id', $tenantId);
            })
            ->sum('jumlah');

        $this->sisaKas = $this->totalUangMasuk - $this->totalUangKeluar;
        
        $this->totalPaguKeseluruhan = \App\Models\DetailBelanja::whereHas('rekening.subKegiatan.kegiatan.program', function ($q) use ($activeYear, $tenantId) {
            $q->where('tahun_anggaran', $activeYear);
            if ($tenantId)
                $q->where('instansi_id', $tenantId);
        })->sum('pagu');
        
        $this->overallPercentage = $this->totalPaguKeseluruhan > 0 ? ($this->totalUangKeluar / $this->totalPaguKeseluruhan) * 100 : 0;
    }

    public function getProgramsProperty()
    {
        $activeYear = ActiveYear::get();
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;
        $search = strtolower($this->searchQuery);

        $query = Program::query()
            ->where('tahun_anggaran', $activeYear)
            ->when($tenantId, fn($q) => $q->where('instansi_id', $tenantId))
            ->with(['kegiatans.subKegiatans.rekenings.detailBelanjas'])
            ->get();

        if (empty($search)) {
            return $query;
        }

        // Deep Search Filter Logic (Collection filtering to keep hierarchy intact for matches)
        return $query->filter(function ($program) use ($search) {
            $programMatches = str_contains(strtolower($program->nama_program), $search) || str_contains(strtolower($program->kode_program), $search);
            
            $program->setRelation('kegiatans', $program->kegiatans->filter(function ($kegiatan) use ($search, $programMatches) {
                $kegiatanMatches = $programMatches || str_contains(strtolower($kegiatan->nama_kegiatan), $search) || str_contains(strtolower($kegiatan->kode_kegiatan), $search);
                
                $kegiatan->setRelation('subKegiatans', $kegiatan->subKegiatans->filter(function ($sub) use ($search, $kegiatanMatches) {
                    $subMatches = $kegiatanMatches || str_contains(strtolower($sub->nama_sub_kegiatan), $search) || str_contains(strtolower($sub->kode_sub_kegiatan), $search);
                    
                    $sub->setRelation('rekenings', $sub->rekenings->filter(function ($rek) use ($search, $subMatches) {
                        $rekMatches = $subMatches || str_contains(strtolower($rek->nama_rekening), $search) || str_contains(strtolower($rek->kode_rekening), $search);
                        
                        $rek->setRelation('detailBelanjas', $rek->detailBelanjas->filter(function ($det) use ($search, $rekMatches) {
                            return $rekMatches || str_contains(strtolower($det->nama_detail_belanja), $search);
                        }));
                        
                        return $rekMatches || $rek->detailBelanjas->isNotEmpty();
                    }));
                    
                    return $subMatches || $sub->rekenings->isNotEmpty();
                }));
                
                return $kegiatanMatches || $kegiatan->subKegiatans->isNotEmpty();
            }));
            
            return $programMatches || $program->kegiatans->isNotEmpty();
        });
    }

    protected function getViewData(): array
    {
        return [
            'targetPercentage' => $this->targetPercentage,
            'totalUangMasuk' => $this->totalUangMasuk,
            'totalUangKeluar' => $this->totalUangKeluar,
            'sisaKas' => $this->sisaKas,
            'overallPercentage' => $this->overallPercentage,
            'programs' => $this->programs,
        ];
    }
}
