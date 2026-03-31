<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Program extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    protected $fillable = [
        'tahun_anggaran',
        'kode_program',
        'nama_program',
        'instansi_id',
    ];

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    public function kegiatans(): HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }

    // Get total pagu for this program
    public function getTotalPaguAttribute(): float
    {
        return $this->kegiatans->sum(function ($kegiatan) {
            return $kegiatan->subKegiatans->sum(function ($subKegiatan) {
                return $subKegiatan->rekenings->sum(function ($rekening) {
                    return $rekening->detailBelanjas->sum('pagu');
                });
            });
        });
    }

    // Get total realisasi for this program
    public function getTotalRealisasiAttribute(): float
    {
        return $this->kegiatans->sum(function ($kegiatan) {
            return $kegiatan->subKegiatans->sum(function ($subKegiatan) {
                return $subKegiatan->rekenings->sum(function ($rekening) {
                    return $rekening->detailBelanjas->sum('realisasi_total');
                });
            });
        });
    }
}
