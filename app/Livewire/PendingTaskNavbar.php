<?php

namespace App\Livewire;

use App\Models\Sp2d;
use App\Models\Realisasi;
use Livewire\Component;
use Filament\Facades\Filament;

class PendingTaskNavbar extends Component
{
    public int $count = 0;
    public array $details = [];

    public function mount(): void
    {
        $this->updateCounts();
    }

    public function updateCounts(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $tenantId = Filament::getTenant()?->id;
        $this->details = [];
        $total = 0;

        // Logika untuk Verifikator
        if ($user->hasRole(['verifikator', 'super_admin'])) {
            $sp2d = Sp2d::query()
                ->when($tenantId, fn($q) => $q->where('instansi_id', $tenantId))
                ->where('status_verifikasi', 'draft')
                ->count();
            
            $realisasi = Realisasi::query()
                ->when($tenantId, fn($q) => $q->where('instansi_id', $tenantId))
                ->where('status', 'diajukan')
                ->count();

            if ($sp2d > 0) $this->details['Dana Kas (Draft)'] = $sp2d;
            if ($realisasi > 0) $this->details['SPJ (Diajukan)'] = $realisasi;
            $total += ($sp2d + $realisasi);
        }

        // Logika untuk Pimpinan
        if ($user->hasRole(['pimpinan', 'super_admin'])) {
            $sp2dVal = Sp2d::query()
                ->when($tenantId, fn($q) => $q->where('instansi_id', $tenantId))
                ->where('status_verifikasi', 'diverifikasi')
                ->count();
            
            $realisasiAppr = Realisasi::query()
                ->when($tenantId, fn($q) => $q->where('instansi_id', $tenantId))
                ->where('status', 'verifikasi')
                ->count();

            if ($sp2dVal > 0) $this->details['Dana Kas (Valid)'] = $sp2dVal;
            if ($realisasiAppr > 0) $this->details['SPJ (Approval)'] = $realisasiAppr;
            $total += ($sp2dVal + $realisasiAppr);
        }

        $this->count = $total;
    }

    public function render()
    {
        return view('livewire.pending-task-navbar');
    }
}
