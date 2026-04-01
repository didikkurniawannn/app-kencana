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
        'nomor_register',
        'tanggal_sp2d',
        'jumlah_sp2d',
        'sumber_dana',
        'nama_sumber_dana',
        'sisa_jumlah',
        'is_active',
        'status_verifikasi',
        'keterangan',
        'lokasi_arsip_fisik',
        'arsip_ruang',
        'arsip_box',
        'arsip_rak_type',
        'arsip_filing_cabinet',
        'arsip_sampul',
        'status_arsip',
        'kode_klasifikasi',
        'masa_retensi',
        'tingkat_perkembangan',
        'tahun_retensi',
        'instansi_id',
        'bukti_file',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nomor_register)) {
                $model->nomor_register = $model->generateNomorRegister();
            }
        });
    }

    public function generateNomorRegister(): string
    {
        $year = \Carbon\Carbon::parse($this->tanggal_sp2d ?? now())->year;
        $instansi = $this->instansi;
        $kodeInstansi = $instansi ? $instansi->kode : 'KENCANA';
        
        $lastRecord = static::where('instansi_id', $this->instansi_id)
            ->whereYear('created_at', now()->year)
            ->whereNotNull('nomor_register')
            ->orderBy('id', 'desc')
            ->first();

        $lastNumber = 0;
        if ($lastRecord && preg_match('/(\d+)$/', $lastRecord->nomor_register, $matches)) {
            $lastNumber = (int)$matches[1];
        }

        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        // Format: [KODE-INSTANSI]/[YEAR]/[SEQ]
        return "{$kodeInstansi}/{$year}/{$newNumber}";
    }

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
            ->where('status', 'disetujui')
            ->sum('jumlah');

        $this->update([
            'sisa_jumlah' => $this->jumlah_sp2d - $totalRealisasi,
        ]);
    }
}
