<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class BkuExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected Collection $data;
    protected string $tabLabel;

    public function __construct(Collection $data, string $tabLabel = 'Semua')
    {
        $this->data = $data;
        $this->tabLabel = $tabLabel;
    }

    public function title(): string
    {
        return 'BKU - ' . $this->tabLabel;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Uraian',
            'Nomor Bukti',
            'Sumber Dana',
            'Kegiatan',
            'Jenis Pengeluaran',
            'Uang Masuk',
            'Uang Keluar',
            'Saldo',
        ];
    }

    public function array(): array
    {
        $rows = [];
        $no = 1;

        foreach ($this->data as $entry) {
            $rows[] = [
                $no++,
                $entry['tanggal'] ? $entry['tanggal']->format('d/m/Y') : '-',
                $entry['uraian'],
                $entry['nomor_bukti'],
                $entry['sumber_dana'],
                $entry['kegiatan'],
                $entry['expense_type'] ?? '-',
                $entry['uang_masuk'] > 0 ? $entry['uang_masuk'] : '-',
                $entry['uang_keluar'] > 0 ? $entry['uang_keluar'] : '-',
                $entry['saldo'],
            ];
        }

        // Summary row
        $rows[] = [
            '',
            '',
            'TOTAL',
            '',
            '',
            '',
            '',
            $this->data->sum('uang_masuk'),
            $this->data->sum('uang_keluar'),
            $this->data->last()['saldo'] ?? 0,
        ];

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 14,
            'C' => 40,
            'D' => 18,
            'E' => 16,
            'F' => 35,
            'G' => 18,
            'H' => 18,
            'I' => 18,
            'J' => 18,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->data->count() + 2; // +1 header, +1 total

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1a5276'],
                ],
            ],
            $lastRow => [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFe8f5e9'],
                ],
            ],
        ];
    }
}
