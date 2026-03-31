<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealisasiDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'realisasi_id',
        'field_name',
        'field_value',
        'instansi_id',
    ];

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    public function realisasi(): BelongsTo
    {
        return $this->belongsTo(Realisasi::class);
    }
}
