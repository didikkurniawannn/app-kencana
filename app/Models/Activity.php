<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Filament\Facades\Filament;

class Activity extends SpatieActivity
{
    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'batch_uuid',
        'event',
        'instansi_id',
        'ip_address',
        'user_agent',
    ];

    protected static function booted()
    {
        static::creating(function ($activity) {
            // Auto-set tenant
            $tenant = Filament::getTenant();
            if ($tenant && empty($activity->instansi_id)) {
                $activity->instansi_id = $tenant->id;
            }

            // Auto-capture IP & User-Agent
            if (app()->runningInConsole() === false) {
                $request = request();
                if (empty($activity->ip_address)) {
                    $activity->ip_address = $request->ip();
                }
                if (empty($activity->user_agent)) {
                    $activity->user_agent = $request->userAgent();
                }
            }
        });
    }

    public function instansi(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Instansi::class);
    }

    /**
     * Get parsed browser/device info from user_agent
     */
    public function getBrowserAttribute(): string
    {
        $ua = $this->user_agent ?? '';
        if (empty($ua)) return '-';

        // Simple browser detection
        if (str_contains($ua, 'Edg/')) return 'Edge';
        if (str_contains($ua, 'Chrome/') && !str_contains($ua, 'Edg/')) return 'Chrome';
        if (str_contains($ua, 'Firefox/')) return 'Firefox';
        if (str_contains($ua, 'Safari/') && !str_contains($ua, 'Chrome/')) return 'Safari';
        if (str_contains($ua, 'Opera') || str_contains($ua, 'OPR/')) return 'Opera';

        return 'Other';
    }

    /**
     * Get parsed OS info from user_agent
     */
    public function getDeviceOsAttribute(): string
    {
        $ua = $this->user_agent ?? '';
        if (empty($ua)) return '-';

        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS')) return 'macOS';
        if (str_contains($ua, 'Linux') && !str_contains($ua, 'Android')) return 'Linux';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) return 'iOS';

        return 'Other';
    }
}
