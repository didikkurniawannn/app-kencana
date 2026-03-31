<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'detail_belanja_id',
        'kuefisien_lama',
        'kuefisien_baru',
        'pagu_lama',
        'pagu_baru',
        'perubahan',
        'keterangan',
        'user_id',
    ];

    protected $casts = [
        'kuefisien_lama' => 'decimal:2',
        'kuefisien_baru' => 'decimal:2',
        'pagu_lama' => 'decimal:2',
        'pagu_baru' => 'decimal:2',
        'perubahan' => 'decimal:2',
    ];

    public function detailBelanja(): BelongsTo
    {
        return $this->belongsTo(DetailBelanja::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
