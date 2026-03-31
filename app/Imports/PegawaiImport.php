<?php

namespace App\Imports;

use App\Models\Pegawai;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Filament\Facades\Filament;

class PegawaiImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['nip']) || empty($row['nama'])) {
            return null;
        }

        $instansiId = Filament::getTenant()?->id;

        return Pegawai::updateOrCreate(
            [
                'nip' => $row['nip'],
                'instansi_id' => $instansiId, // Ensure uniqueness within tenant
            ],
            [
                'nama' => $row['nama'],
                'pangkat' => $row['pangkat'] ?? null,
                'golongan' => $row['golongan'] ?? null,
                'jabatan' => $row['jabatan'] ?? null,
            ]
        );
    }
}
