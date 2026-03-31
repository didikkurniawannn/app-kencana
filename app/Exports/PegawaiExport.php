<?php

namespace App\Exports;

use App\Models\Pegawai;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Filament\Facades\Filament;

class PegawaiExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // Scope to current tenant if available
        $query = Pegawai::query();
        if (Filament::getTenant()) {
            $query->where('instansi_id', Filament::getTenant()->id);
        }
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'NIP',
            'Nama',
            'Pangkat',
            'Golongan',
            'Jabatan',
        ];
    }

    public function map($pegawai): array
    {
        return [
            $pegawai->nip,
            $pegawai->nama,
            $pegawai->pangkat,
            $pegawai->golongan,
            $pegawai->jabatan,
        ];
    }
}
