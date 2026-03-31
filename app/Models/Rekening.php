<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rekening extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_rekening',
        'nama_rekening',
        'sub_kegiatan_id',
        'instansi_id',
    ];

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(SubKegiatan::class);
    }

    public function detailBelanjas(): HasMany
    {
        return $this->hasMany(DetailBelanja::class);
    }

    public function getTotalPaguAttribute(): float
    {
        return $this->detailBelanjas->sum('pagu');
    }

    public function getTotalRealisasiAttribute(): float
    {
        return $this->detailBelanjas->sum('realisasi_total');
    }

    public function getTotalKuefisienAttribute(): float
    {
        return $this->detailBelanjas->sum('kuefisien_murni');
    }

    public function getTotalKuefisienRealisasiAttribute(): float
    {
        return \App\Models\Realisasi::where('status', 'approved')
            ->whereIn('detail_belanja_id', $this->detailBelanjas->pluck('id'))
            ->sum('kuefisien');
    }
}
