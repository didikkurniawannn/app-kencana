<?php

namespace App\Imports;

use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Models\Rekening;
use App\Models\DetailBelanja;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RkaImporter implements ToCollection, WithHeadingRow
{
    protected $tahun;
    protected $instansiId;

    public function __construct(int $tahun, int $instansiId)
    {
        $this->tahun = $tahun;
        $this->instansiId = $instansiId;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['kode_program']) || empty($row['kode_rekening'])) {
                continue;
            }

            // 1. Program
            $program = Program::updateOrCreate(
                [
                    'tahun_anggaran' => $this->tahun,
                    'kode_program' => $row['kode_program'],
                    'instansi_id' => $this->instansiId,
                ],
                ['nama_program' => $row['nama_program']]
            );

            // 2. Kegiatan
            $kegiatan = Kegiatan::updateOrCreate(
                [
                    'program_id' => $program->id,
                    'kode_kegiatan' => $row['kode_kegiatan'],
                    'instansi_id' => $this->instansiId,
                ],
                ['nama_kegiatan' => $row['nama_kegiatan']]
            );

            // 3. Sub Kegiatan
            $subKegiatan = SubKegiatan::updateOrCreate(
                [
                    'kegiatan_id' => $kegiatan->id,
                    'kode_sub_kegiatan' => $row['kode_sub_kegiatan'],
                    'instansi_id' => $this->instansiId,
                ],
                ['nama_sub_kegiatan' => $row['nama_sub_kegiatan']]
            );

            // 4. Rekening
            $rekening = Rekening::updateOrCreate(
                [
                    'sub_kegiatan_id' => $subKegiatan->id,
                    'kode_rekening' => $row['kode_rekening'],
                    'instansi_id' => $this->instansiId,
                ],
                ['nama_rekening' => $row['nama_rekening']]
            );

            // 5. Detail Belanja
            $kuefisien = (float) ($row['jumlah_kuefisien'] ?? 0);
            $harga = (float) ($row['harga_satuan'] ?? 0);
            $pagu = $kuefisien * $harga;

            DetailBelanja::updateOrCreate(
                [
                    'rekening_id' => $rekening->id,
                    'nama_detail_belanja' => $row['nama_detail_belanja'],
                    'instansi_id' => $this->instansiId,
                ],
                [
                    'kuefisien' => $kuefisien,
                    'harga' => $harga,
                    'pagu' => $pagu,
                    'satuan' => $row['satuan'] ?? null,
                ]
            );
        }
    }
}
