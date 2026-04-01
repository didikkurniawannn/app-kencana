<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Realisasi extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    protected $fillable = [
        'detail_belanja_id',
        'expense_type_id',
        'user_id',
        'pegawai_id',
        'sp2d_id',
        'nomor_register',
        'tanggal_realisasi',
        'kuefisien',
        'jumlah',
        'keterangan',
        'bukti_file',
        'status',
        'instansi_id',
        'arsip_ruang',
        'arsip_box',
        'arsip_rak_type',
        'arsip_filing_cabinet',
        'arsip_sampul',
        'status_arsip',
        'kode_klasifikasi',
        'masa_retensi',
    ];

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    protected $casts = [
        'tanggal_realisasi' => 'date',
        'kuefisien' => 'decimal:2',
        'jumlah' => 'decimal:2',
        'bukti_file' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nomor_register)) {
                $model->nomor_register = $model->generateNomorRegister();
            }
        });

        // Validate budget and SP2D balance before saving
        static::saving(function ($model) {
            $model->validateBudget();
            $model->validateSp2dBalance();
        });

        // Update detail_belanja realisasi_total and SP2D balance after save
        static::saved(function ($model) {
            if ($model->detailBelanja) {
                $model->detailBelanja->updateRealisasiTotal();
            }
            if ($model->sp2d) {
                $model->sp2d->updateSisaJumlah();
            }
        });

        static::deleted(function ($model) {
            if ($model->detailBelanja) {
                $model->detailBelanja->updateRealisasiTotal();
            }
            if ($model->sp2d) {
                $model->sp2d->updateSisaJumlah();
            }
        });
    }

    public function validateSp2dBalance(): void
    {
        if (!$this->sp2d_id) {
            return;
        }

        $sp2d = $this->sp2d;

        // Only check for approved status or if it's already approved
        if ($this->status === 'disetujui') {
            // Calculate already approved realisasi (excluding current if updating)
            $existingApprovedInSp2d = $sp2d->realisasis()
                ->where('status', 'disetujui')
                ->where('id', '!=', $this->id ?? 0)
                ->sum('jumlah');

            $totalRequested = $existingApprovedInSp2d + $this->jumlah;

            if ($totalRequested > $sp2d->jumlah_sp2d) {
                $sisaCair = $sp2d->jumlah_sp2d - $existingApprovedInSp2d;
                throw ValidationException::withMessages([
                    'sp2d_id' => "Saldo kas SP2D tidak mencukupi. Sisa yang bisa dicairkan: Rp " . number_format((float) $sisaCair, 0, ',', '.'),
                ]);
            }
        }
    }

    public function validateBudget(): void
    {
        if (!$this->detail_belanja_id) {
            return;
        }

        $detailBelanja = $this->detailBelanja;

        if (!$detailBelanja) {
            return;
        }

        // Calculate already approved realisasi (excluding current if updating)
        $existingApproved = $detailBelanja->realisasis()
            ->where('status', 'disetujui')
            ->where('id', '!=', $this->id ?? 0)
            ->sum('jumlah');

        // If this realisasi will be approved, check budget
        if ($this->status === 'disetujui') {
            $totalAfterThis = $existingApproved + $this->jumlah;
            $sisaPagu = $detailBelanja->pagu - $existingApproved;

            if ($this->jumlah > $sisaPagu) {
                throw ValidationException::withMessages([
                    'jumlah' => "Input realisasi (Rp " . number_format((float) $this->jumlah, 0, ',', '.') .
                        ") melebihi sisa pagu (Rp " . number_format((float) $sisaPagu, 0, ',', '.') .
                        "). Silakan kurangi jumlah realisasi.",
                ]);
            }
        }
    }

    public function detailBelanja(): BelongsTo
    {
        return $this->belongsTo(DetailBelanja::class);
    }

    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function sp2d(): BelongsTo
    {
        return $this->belongsTo(Sp2d::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(RealisasiDetail::class);
    }
    
    public function logs(): HasMany
    {
        return $this->hasMany(RealisasiLog::class)->orderBy('created_at', 'desc');
    }

    public function generateNomorRegister(): string
    {
        $year = \Carbon\Carbon::parse($this->tanggal_realisasi ?? now())->year;
        $instansi = $this->instansi ?: auth()->user()?->instansi;
        $kodeInstansi = $instansi ? $instansi->kode : 'KENCANA';
        
        $lastRecord = static::where('instansi_id', $this->instansi_id ?? ($instansi->id ?? null))
            ->whereYear('created_at', now()->year)
            ->whereNotNull('nomor_register')
            ->orderBy('id', 'desc')
            ->first();

        $lastNumber = 0;
        if ($lastRecord && preg_match('/(\d+)$/', $lastRecord->nomor_register, $matches)) {
            $lastNumber = (int)$matches[1];
        }

        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        // Format: REA/[KODE-INSTANSI]/[YEAR]/[SEQ]
        return "REA/{$kodeInstansi}/{$year}/{$newNumber}";
    }

    // Get detail value by field name
    public function getDetailValue(string $fieldName): ?string
    {
        return $this->details->where('field_name', $fieldName)->first()?->field_value;
    }
}
