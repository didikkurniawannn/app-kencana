<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DetailBelanja extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    protected $fillable = [
        'rekening_id',
        'nama_detail_belanja',
        'kuefisien',
        'kuefisien_murni',
        'satuan',
        'harga',
        'pagu_murni',
        'pagu',
        'realisasi_total',
        'sisa_pagu',
        'instansi_id',
    ];

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    protected $casts = [
        'kuefisien' => 'decimal:2',
        'kuefisien_murni' => 'decimal:2',
        'harga' => 'decimal:2',
        'pagu_murni' => 'decimal:2',
        'pagu' => 'decimal:2',
        'realisasi_total' => 'decimal:2',
        'sisa_pagu' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Auto initialize murni values if not set
            if ($model->kuefisien && !$model->kuefisien_murni) {
                $model->kuefisien_murni = $model->kuefisien;
            }
            if ($model->kuefisien && $model->harga && !$model->pagu_murni) {
                $model->pagu_murni = $model->kuefisien * $model->harga;
            }
        });

        static::saving(function ($model) {
            // Auto calculate pagu if kuefisien and harga are set
            if ($model->kuefisien && $model->harga) {
                $model->pagu = $model->kuefisien * $model->harga;
            }

            // Ensure murni values are set if still null
            if (!$model->kuefisien_murni) {
                $model->kuefisien_murni = $model->kuefisien;
            }
            if (!$model->pagu_murni) {
                $model->pagu_murni = $model->pagu;
            }

            $model->sisa_pagu = $model->pagu - $model->realisasi_total;
        });
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class);
    }

    public function realisasis(): HasMany
    {
        return $this->hasMany(Realisasi::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(BudgetRevision::class);
    }

    // Get full hierarchy path
    public function getFullPathAttribute(): string
    {
        $rekening = $this->rekening;
        $subKegiatan = $rekening?->subKegiatan;
        $kegiatan = $subKegiatan?->kegiatan;
        $program = $kegiatan?->program;

        return implode(' > ', array_filter([
            $program?->kode_program,
            $kegiatan?->kode_kegiatan,
            $subKegiatan?->kode_sub_kegiatan,
            $rekening?->kode_rekening,
        ]));
    }

    public function getSisaKuefisienAttribute(): float
    {
        if ($this->harga <= 0) {
            return 0;
        }

        return (float) ($this->sisa_pagu / $this->harga);
    }

    // Get hierarchical directory path for file uploads
    // Get hierarchical directory path for file uploads
    public function getDirectoryPath(?string $date = null): string
    {
        $rekening = $this->rekening;
        $subKegiatan = $rekening?->subKegiatan;
        $kegiatan = $subKegiatan?->kegiatan;
        $program = $kegiatan?->program;
        $tahun = $program?->tahun_anggaran ?? 'Unknown Year';

        $clean = fn($string) => str_replace(['/', '\\'], '-', $string);

        $path = [
            'bukti-realisasi',
            $tahun,
            $program ? $program->kode_program . ' ' . $clean($program->nama_program) : 'Unknown Program',
            $kegiatan ? $kegiatan->kode_kegiatan . ' ' . $clean($kegiatan->nama_kegiatan) : 'Unknown Kegiatan',
            $subKegiatan ? $subKegiatan->kode_sub_kegiatan . ' ' . $clean($subKegiatan->nama_sub_kegiatan) : 'Unknown Sub Kegiatan',
            $clean($this->nama_detail_belanja),
        ];

        if ($date) {
            $path[] = \Carbon\Carbon::parse($date)->format('d-m-Y');
        }

        return implode('/', array_filter($path));
    }

    // Update realisasi_total
    public function updateRealisasiTotal(): void
    {
        $this->realisasi_total = $this->realisasis()
            ->where('status', 'disetujui')
            ->sum('jumlah');
        $this->save();
    }
}
