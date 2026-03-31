<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealisasiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'realisasi_id',
        'user_id',
        'action',
        'catatan',
    ];

    public function realisasi(): BelongsTo
    {
        return $this->belongsTo(Realisasi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
