<?php

namespace App\Exports;

use App\Models\Realisasi;
use App\Models\ExpenseField;
use App\Models\ExpenseType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class RealisasiExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $query;
    protected $dynamicFields;
    protected ?array $expenseTypeIds;

    public function __construct($query, ?array $expenseTypeIds = null)
    {
        $this->query = $query;
        $this->expenseTypeIds = $expenseTypeIds;

        // Fetch dynamic fields only for the filtered expense types
        $fieldQuery = ExpenseField::select('field_name', 'field_label');

        if ($this->expenseTypeIds && count($this->expenseTypeIds) > 0) {
            $fieldQuery->whereIn('expense_type_id', $this->expenseTypeIds);
        }

        $this->dynamicFields = $fieldQuery
            ->get()
            ->unique('field_name')
            ->values();
    }

    public function collection()
    {
        return $this->query
            ->with(['detailBelanja.rekening.subKegiatan.kegiatan.program', 'expenseType', 'sp2d', 'pegawai', 'details'])
            ->get();
    }

    public function headings(): array
    {
        $headings = [
            'No',
            'Tanggal Realisasi',
            'Jenis Pengeluaran',
            'Program',
            'Kegiatan',
            'Sub Kegiatan',
            'Kode Rekening',
            'Nama Rekening',
            'Detail Belanja',
            'Jumlah Realisasi',
            'Kuefisien',
            'SP2D',
            'Sumber Dana',
            'Pegawai',
            'NIP',
            'Keterangan',
            'Status',
        ];

        // Add dynamic field labels as headings (only relevant to the filtered expense types)
        foreach ($this->dynamicFields as $field) {
            $headings[] = $field->field_label;
        }

        return $headings;
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        $detail = $row->detailBelanja;
        $rekening = $detail?->rekening;
        $subKegiatan = $rekening?->subKegiatan;
        $kegiatan = $subKegiatan?->kegiatan;
        $program = $kegiatan?->program;

        $data = [
            $rowNumber,
            $row->tanggal_realisasi ? $row->tanggal_realisasi->format('d/m/Y') : '-',
            $row->expenseType?->name ?? '-',
            $program?->nama_program ?? '-',
            $kegiatan?->nama_kegiatan ?? '-',
            $subKegiatan?->nama_sub_kegiatan ?? '-',
            $rekening?->kode_rekening ?? '-',
            $rekening?->nama_rekening ?? '-',
            $detail?->nama_detail_belanja ?? '-',
            $row->jumlah,
            $row->kuefisien,
            $row->sp2d?->nomor_sp2d ?? '-',
            $row->sp2d?->sumber_dana ?? '-',
            $row->pegawai?->nama ?? '-',
            $row->pegawai?->nip ?? '-',
            $row->keterangan ?? '-',
            $row->status ?? '-',
        ];

        // Map dynamic field values (only relevant fields)
        foreach ($this->dynamicFields as $field) {
            $value = $row->details->where('field_name', $field->field_name)->first()?->field_value;
            $data[] = $value ?? '-';
        }

        return $data;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2D7D46'],
                ],
            ],
        ];
    }
}
