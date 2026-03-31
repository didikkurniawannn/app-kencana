<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    protected $fillable = [
        'kode_kegiatan',
        'nama_kegiatan',
        'program_id',
        'instansi_id',
    ];

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function subKegiatans(): HasMany
    {
        return $this->hasMany(SubKegiatan::class);
    }

    public function getTotalPaguAttribute(): float
    {
        return $this->subKegiatans->sum(function ($subKegiatan) {
            return $subKegiatan->rekenings->sum(function ($rekening) {
                return $rekening->detailBelanjas->sum('pagu');
            });
        });
    }

    public function getTotalRealisasiAttribute(): float
    {
        return $this->subKegiatans->sum(function ($subKegiatan) {
            return $subKegiatan->rekenings->sum(function ($rekening) {
                return $rekening->detailBelanjas->sum('realisasi_total');
            });
        });
    }
}
