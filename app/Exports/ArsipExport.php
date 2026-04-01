<?php

namespace App\Exports;

use App\Models\Realisasi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ArsipExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query
            ->with(['detailBelanja.rekening.subKegiatan.kegiatan.program', 'instansi', 'sp2d'])
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Register',
            'Tanggal Realisasi',
            'Instansi',
            'Program',
            'Kegiatan',
            'Detail Belanja (Sesuai Kuitansi)',
            'Sumber Dana',
            'Ruang',
            'Box',
            'Rak / Roll o Pact',
            'Filing Cabinet',
            'Nama Sampul',
            'Kode Klasifikasi',
        ];
    }

    public function map($row): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        $detail = $row->detailBelanja;
        $rekening = $detail?->rekening;
        $subKegiatan = $rekening?->subKegiatan;
        $kegiatan = $subKegiatan?->kegiatan;
        $program = $kegiatan?->program;

        return [
            $rowNumber,
            $row->nomor_register ?? '-',
            $row->tanggal_realisasi ? $row->tanggal_realisasi->format('d/m/Y') : '-',
            $row->instansi?->name ?? '-',
            $program?->nama_program ?? '-',
            $kegiatan?->nama_kegiatan ?? '-',
            $detail?->nama_detail_belanja ?? '-',
            $row->sp2d?->sumber_dana ?? '-',
            $row->arsip_ruang ?? '-',
            $row->arsip_box ?? '-',
            $row->arsip_rak_type ?? '-',
            $row->arsip_filing_cabinet ?? '-',
            $row->arsip_sampul ?? '-',
            $row->kode_klasifikasi ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Auto size columns
        foreach (range('A', 'N') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E40AF'], // Nice Blue
                ],
            ],
        ];
    }
}
