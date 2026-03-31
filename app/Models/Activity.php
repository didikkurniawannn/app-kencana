<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Filament\Facades\Filament;

class Activity extends SpatieActivity
{
    protected static function booted()
    {
        static::creating(function ($activity) {
            $tenant = Filament::getTenant();
            if ($tenant && empty($activity->instansi_id)) {
                $activity->instansi_id = $tenant->id;
            }
        });
    }

    public function instansi(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Instansi::class);
    }
}
