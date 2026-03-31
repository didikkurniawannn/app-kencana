<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubKegiatan extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    protected $fillable = [
        'kode_sub_kegiatan',
        'nama_sub_kegiatan',
        'kegiatan_id',
        'instansi_id',
    ];

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function rekenings(): HasMany
    {
        return $this->hasMany(Rekening::class);
    }

    public function getTotalPaguAttribute(): float
    {
        return $this->rekenings->sum(function ($rekening) {
            return $rekening->detailBelanjas->sum('pagu');
        });
    }

    public function getTotalRealisasiAttribute(): float
    {
        return $this->rekenings->sum(function ($rekening) {
            return $rekening->detailBelanjas->sum('realisasi_total');
        });
    }
}
