<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ActivityExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths
{
    protected Collection $data;
    protected string $title;

    public function __construct(Collection $data, string $title = 'Log Aktivitas')
    {
        $this->data = $data;
        $this->title = $title;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Waktu (WIB)',
            'Pelaku',
            'Email Pelaku',
            'Aksi',
            'Modul/Model',
            'ID Subjek',
            'Keterangan',
            'Data Perubahan (JSON)',
            'IP Address',
            'User Agent',
        ];
    }

    /**
     * @param mixed $row
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y H:i:s') : '-',
            $row->causer?->name ?? 'Sistem',
            $row->causer?->email ?? '-',
            $row->event,
            $row->subject_type ?? '-',
            $row->subject_id ?? '-',
            $row->description,
            json_encode($row->properties, JSON_PRETTY_PRINT),
            $row->ip_address ?? '-',
            $row->user_agent ?? '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 20,
            'C' => 20,
            'D' => 25,
            'E' => 15,
            'F' => 30,
            'G' => 12,
            'H' => 45,
            'I' => 60,
            'J' => 18,
            'K' => 50,
        ];
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
                    'startColor' => ['argb' => 'FF1a5276'],
                ],
            ],
            // Alignment for multi-line description and JSON
            'H' => ['alignment' => ['wrapText' => true]],
            'I' => ['alignment' => ['wrapText' => true, 'vertical' => 'top']],
            'K' => ['alignment' => ['wrapText' => true]],
        ];
    }
}
