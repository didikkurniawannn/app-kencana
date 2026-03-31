<?php

namespace App\Exports;

use App\Models\DetailBelanja;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetailBelanjaExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function query()
    {
        return DetailBelanja::query()
            ->with(['rekening.subKegiatan.kegiatan.program'])
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Program',
            'Nama Program',
            'Kode Kegiatan',
            'Kode Sub Kegiatan',
            'Kode Rekening',
            'Nama Rekening',
            'Detail Belanja',
            'Kuefisien',
            'Satuan',
            'Harga (Rp)',
            'Pagu (Rp)',
            'Realisasi (Rp)',
            'Sisa Pagu (Rp)',
            '% Realisasi',
        ];
    }

    public function map($detail): array
    {
        static $no = 0;
        $no++;

        $rekening = $detail->rekening;
        $subKegiatan = $rekening?->subKegiatan;
        $kegiatan = $subKegiatan?->kegiatan;
        $program = $kegiatan?->program;

        $persentase = $detail->pagu > 0
            ? round(($detail->realisasi_total / $detail->pagu) * 100, 1)
            : 0;

        return [
            $no,
            $program?->kode_program ?? '-',
            $program?->nama_program ?? '-',
            $kegiatan?->kode_kegiatan ?? '-',
            $subKegiatan?->kode_sub_kegiatan ?? '-',
            $rekening?->kode_rekening ?? '-',
            $rekening?->nama_rekening ?? '-',
            $detail->nama_detail_belanja,
            $detail->kuefisien,
            $detail->satuan,
            $detail->harga,
            $detail->pagu,
            $detail->realisasi_total,
            $detail->sisa_pagu,
            $persentase . '%',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
