<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseField extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_type_id',
        'field_name',
        'field_type',
        'field_label',
        'is_required',
        'options',
        'order',
        'instansi_id',
    ];

    public function instansi(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
    ];

    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class);
    }
}
