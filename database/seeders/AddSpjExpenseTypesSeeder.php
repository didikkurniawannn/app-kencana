<?php

namespace Database\Seeders;

use App\Models\ExpenseType;
use Illuminate\Database\Seeder;

class AddSpjExpenseTypesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SPJ RUTIN
        $spjRutin = ExpenseType::create([
            'name' => 'SPJ RUTIN',
            'slug' => 'spj-rutin',
            'description' => 'SPJ Rutin (Gaji, Tunjangan, BPJS, Air, Pajak, Honor)',
            'is_active' => true,
        ]);

        $rutinFields = [
            [
                'field_name' => 'jenis_spj_rutin',
                'field_type' => 'select',
                'field_label' => 'Jenis SPJ Rutin',
                'is_required' => true,
                'order' => 1,
                'options' => [
                    'Gaji Pegawai' => 'Gaji Pegawai',
                    'Tunjangan' => 'Tunjangan',
                    'BPJS' => 'BPJS',
                    'Air' => 'Air',
                    'Pajak' => 'Pajak',
                    'Honor' => 'Honor',
                ]
            ],
            [
                'field_name' => 'keterangan_pembayaran',
                'field_type' => 'textarea',
                'field_label' => 'Keterangan Pembayaran',
                'is_required' => false,
                'order' => 2
            ],
        ];

        foreach ($rutinFields as $field) {
            $spjRutin->fields()->create($field);
        }

        // 2. SPJ BMHP
        $spjBmhp = ExpenseType::create([
            'name' => 'SPJ BMHP',
            'slug' => 'spj-bmhp',
            'description' => 'SPJ Barang Milik Habis Pakai (ATK, Air Minum, Logistik, dll)',
            'is_active' => true,
        ]);

        $bmhpFields = [
            [
                'field_name' => 'jenis_belanja',
                'field_type' => 'select',
                'field_label' => 'Jenis Belanja',
                'is_required' => true,
                'order' => 1,
                'options' => [
                    'ATK' => 'ATK',
                    'Air Minum' => 'Air Minum',
                    'Logistik' => 'Logistik',
                    'Lainnya' => 'Lainnya',
                ]
            ],
            [
                'field_name' => 'keterangan_pembelian',
                'field_type' => 'textarea',
                'field_label' => 'Keterangan Pembelian',
                'is_required' => false,
                'order' => 2
            ],
        ];

        foreach ($bmhpFields as $field) {
            $spjBmhp->fields()->create($field);
        }
    }
}
