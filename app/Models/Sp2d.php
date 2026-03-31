<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Sp2d extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    protected $fillable = [
        'nomor_sp2d',
        'tanggal_sp2d',
        'jumlah_sp2d',
        'sumber_dana',
        'nama_sumber_dana',
        'sisa_jumlah',
        'is_active',
        'status_verifikasi',
        'keterangan',
        'lokasi_arsip_fisik',
        'status_arsip',
        'kode_klasifikasi',
        'masa_retensi',
        'tingkat_perkembangan',
        'tahun_retensi',
        'instansi_id',
        'bukti_file',
    ];

    public function instansi(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    protected $casts = [
        'tanggal_sp2d' => 'date',
        'jumlah_sp2d' => 'integer',
        'sisa_jumlah' => 'integer',
        'bukti_file' => 'array',
    ];

    public function realisasis(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Realisasi::class);
    }

    public function updateSisaJumlah(): void
    {
        $totalRealisasi = $this->realisasis()
            ->where('status', 'approved')
            ->sum('jumlah');

        $this->update([
            'sisa_jumlah' => $this->jumlah_sp2d - $totalRealisasi,
        ]);
    }
}
