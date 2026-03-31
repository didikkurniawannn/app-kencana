<?php

namespace App\Exports;

use App\Models\ExpenseField;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RealisasiBpkTemplateExport implements FromArray, WithHeadings, WithStyles
{
    protected ?int $expenseTypeId;
    protected array $dynamicFieldNames = [];

    public function __construct(?int $expenseTypeId = null)
    {
        $this->expenseTypeId = $expenseTypeId;

        if ($this->expenseTypeId) {
            $this->dynamicFieldNames = ExpenseField::where('expense_type_id', $this->expenseTypeId)
                ->orderBy('order')
                ->pluck('field_label', 'field_name')
                ->toArray();
        }
    }

    public function headings(): array
    {
        $headings = [
            'kode_rekening',
            'nama_detail_belanja',
            'tanggal_realisasi',
            'jumlah',
            'kuefisien',
            'nomor_sp2d',
            'keterangan',
        ];

        // Add dynamic field names as headings (using field_name for import compatibility)
        foreach ($this->dynamicFieldNames as $fieldName => $fieldLabel) {
            $headings[] = $fieldName;
        }

        return $headings;
    }

    public function array(): array
    {
        // Return one example row
        $example = [
            '5.1.02.01.01.0001',       // kode_rekening
            'Contoh Detail Belanja',    // nama_detail_belanja
            '2026-01-15',               // tanggal_realisasi
            '1500000',                  // jumlah
            '1',                        // kuefisien
            'SP2D-001',                 // nomor_sp2d
            'Contoh keterangan',        // keterangan
        ];

        // Add example values for dynamic fields
        foreach ($this->dynamicFieldNames as $fieldName => $fieldLabel) {
            $example[] = '';
        }

        return [$example];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();

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
            2 => [
                'font' => [
                    'italic' => true,
                    'color' => ['argb' => 'FF999999'],
                ],
            ],
        ];
    }
}
