<?php

namespace Database\Seeders;

use App\Models\ExpenseField;
use App\Models\ExpenseType;
use Illuminate\Database\Seeder;

class MakanMinumFieldsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Hotel
        $hotel = ExpenseType::updateOrCreate(
            ['name' => 'Makan dan Minum (Hotel)'],
            [
                'id' => 4,
                'slug' => 'makan-minum-hotel',
                'is_active' => true
            ]
        );

        // Delete existing fields if any
        ExpenseField::where('expense_type_id', $hotel->id)->delete();

        $hotelFields = [
            ['field_label' => 'Nama Kegiatan', 'field_name' => 'nama_kegiatan', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Tanggal Pelaksanaan', 'field_name' => 'tanggal_pelaksanaan', 'field_type' => 'date', 'is_required' => true],
            ['field_label' => 'Tempat Pelaksanaan', 'field_name' => 'tempat_pelaksanaan', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Penyedia', 'field_name' => 'penyedia', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Paket Kegiatan', 'field_name' => 'paket_kegiatan', 'field_type' => 'select', 'is_required' => true, 'options' => ['Full Board' => 'Full Board', 'Tidak Full Board' => 'Tidak Full Board']],
            ['field_label' => 'Nomor SP2D', 'field_name' => 'nomor_sp2d', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Tanggal SP2D', 'field_name' => 'tanggal_sp2d', 'field_type' => 'date', 'is_required' => true],
            ['field_label' => 'Jumlah SP2D', 'field_name' => 'nilai_sp2d', 'field_type' => 'number', 'is_required' => true],
            ['field_label' => 'Biaya (Rp)', 'field_name' => 'biaya_realisasi', 'field_type' => 'number', 'is_required' => true],
        ];

        foreach ($hotelFields as $index => $field) {
            ExpenseField::create(array_merge($field, [
                'expense_type_id' => $hotel->id,
                'order' => $index,
            ]));
        }

        // 2. Katering
        $katering = ExpenseType::updateOrCreate(
            ['name' => 'Makan dan Minum (Katering)'],
            [
                'id' => 5,
                'slug' => 'makan-minum-katering',
                'is_active' => true
            ]
        );

        ExpenseField::where('expense_type_id', $katering->id)->delete();

        $kateringFields = [
            ['field_label' => 'Nama Kegiatan', 'field_name' => 'nama_kegiatan', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Tanggal Pelaksanaan', 'field_name' => 'tanggal_pelaksanaan', 'field_type' => 'date', 'is_required' => true],
            ['field_label' => 'Tempat Pelaksanaan', 'field_name' => 'tempat_pelaksanaan', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Penyedia Jasa', 'field_name' => 'penyedia_jasa', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Alamat Penyedia Jasa', 'field_name' => 'alamat_penyedia', 'field_type' => 'textarea', 'is_required' => true],
            ['field_label' => 'NPWP Penyedia Jasa', 'field_name' => 'npwp_penyedia', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Nomor SP2D', 'field_name' => 'nomor_sp2d', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Tanggal SP2D', 'field_name' => 'tanggal_sp2d', 'field_type' => 'date', 'is_required' => true],
            ['field_label' => 'Jumlah SP2D', 'field_name' => 'nilai_sp2d', 'field_type' => 'number', 'is_required' => true],
            ['field_label' => 'Biaya (Rp)', 'field_name' => 'biaya_realisasi', 'field_type' => 'number', 'is_required' => true],
        ];

        foreach ($kateringFields as $index => $field) {
            ExpenseField::create(array_merge($field, [
                'expense_type_id' => $katering->id,
                'order' => $index,
            ]));
        }

        // Remove old generic "Makan dan Minum" if exists to avoid confusion
        ExpenseType::where('id', 1)->delete();
    }
}
