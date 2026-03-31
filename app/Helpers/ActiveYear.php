<?php

namespace App\Helpers;

use App\Models\Setting;
use App\Models\Program;
use Filament\Facades\Filament;

class ActiveYear
{
    /**
     * Get the currently active year from session, falling back to tenant setting, then current year.
     */
    public static function get(): int
    {
        // 1. Check session first
        $sessionYear = session('active_year');
        if ($sessionYear) {
            return (int) $sessionYear;
        }

        // 2. Fallback to tenant setting
        $settingYear = Setting::get('tahun_anggaran_aktif', date('Y'));

        return (int) $settingYear;
    }

    /**
     * Set the active year in session.
     */
    public static function set(int $year): void
    {
        session(['active_year' => $year]);
    }

    /**
     * Get list of available years from programs table for the current tenant.
     */
    public static function getAvailableYears(): array
    {
        $tenantId = Filament::getTenant()?->id;

        $years = Program::query()
            ->when($tenantId, fn($q) => $q->where('instansi_id', $tenantId))
            ->distinct()
            ->orderByDesc('tahun_anggaran')
            ->pluck('tahun_anggaran')
            ->toArray();

        // Ensure current setting year and current calendar year are included
        $settingYear = (int) Setting::get('tahun_anggaran_aktif', date('Y'));
        $currentYear = (int) date('Y');

        $years = array_unique(array_merge($years, [$settingYear, $currentYear]));
        rsort($years);

        return $years;
    }

    /**
     * Reset the session year (revert to default).
     */
    public static function reset(): void
    {
        session()->forget('active_year');
    }
}
