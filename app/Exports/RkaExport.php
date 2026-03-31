<?php

namespace App\Exports;

use App\Models\DetailBelanja;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class RkaExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Kode Program',
            'Nama Program',
            'Kode Kegiatan',
            'Nama Kegiatan',
            'Kode Sub Kegiatan',
            'Nama Sub Kegiatan',
            'Kode Rekening',
            'Nama Rekening',
            'Nama Detail Belanja',
            'Kuefisien Murni',
            'Kuefisien Perubahan',
            'Satuan',
            'Harga Satuan',
            'Anggaran Murni',
            'Anggaran Perubahan',
            'Selisih (+/-)'
        ];
    }

    /**
     * @param DetailBelanja $row
     */
    public function map($row): array
    {
        $rekening = $row->rekening;
        $subKegiatan = $rekening?->subKegiatan;
        $kegiatan = $subKegiatan?->kegiatan;
        $program = $kegiatan?->program;

        $paguMurni = (float) ($row->pagu_murni ?? $row->pagu);
        $paguPerubahan = (float) $row->pagu;
        $selisih = $paguPerubahan - $paguMurni;

        $kuefisienMurni = (float) ($row->kuefisien_murni ?? $row->kuefisien);
        $kuefisienPerubahan = (float) $row->kuefisien;

        return [
            $program?->kode_program,
            $program?->nama_program,
            $kegiatan?->kode_kegiatan,
            $kegiatan?->nama_kegiatan,
            $subKegiatan?->kode_sub_kegiatan,
            $subKegiatan?->nama_sub_kegiatan,
            $rekening?->kode_rekening,
            $rekening?->nama_rekening,
            $row->nama_detail_belanja,
            $kuefisienMurni,
            $kuefisienPerubahan,
            $row->satuan,
            $row->harga,
            $paguMurni,
            $paguPerubahan,
            $selisih,
        ];
    }
}
