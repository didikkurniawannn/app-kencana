<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseField;
use App\Models\ExpenseType;

class UpdatePemeliharaanFieldsSeeder extends Seeder
{
    public function run(): void
    {
        $type = ExpenseType::where('name', 'Pemeliharaan')->first();
        if (!$type) {
            return;
        }

        $typeId = $type->id;
        ExpenseField::where('expense_type_id', $typeId)->delete();

        $fields = [
            ['field_name' => 'nomor_sp2d', 'field_type' => 'text', 'field_label' => 'Nomor SP2D', 'is_required' => true, 'order' => 1],
            ['field_name' => 'tanggal_sp2d', 'field_type' => 'date', 'field_label' => 'Tanggal SP2D', 'is_required' => true, 'order' => 2],
            ['field_name' => 'nilai_sp2d', 'field_type' => 'number', 'field_label' => 'Nilai SP2D (Rp)', 'is_required' => true, 'order' => 3],
            ['field_name' => 'penyedia', 'field_type' => 'text', 'field_label' => 'Penyedia', 'is_required' => true, 'order' => 4],
            ['field_name' => 'alamat_penyedia', 'field_type' => 'textarea', 'field_label' => 'Alamat Penyedia', 'is_required' => true, 'order' => 5],
            ['field_name' => 'nomor_kerjasama', 'field_type' => 'text', 'field_label' => 'Nomor MoU/Perjanjian/ Kerjasama Lainnya (jika ada)', 'is_required' => false, 'order' => 6],
            ['field_name' => 'tanggal_kerjasama', 'field_type' => 'date', 'field_label' => 'Tanggal MoU/Perjanjian/ Kerjasama Lainnya (jika ada)', 'is_required' => false, 'order' => 7],
            ['field_name' => 'tanggal_mulai', 'field_type' => 'date', 'field_label' => 'Tanggal Mulai Pemeliharaan', 'is_required' => true, 'order' => 8],
            ['field_name' => 'tanggal_berakhir', 'field_type' => 'date', 'field_label' => 'Tanggal Berakhir Pemeliharaan', 'is_required' => true, 'order' => 9],
            ['field_name' => 'jenis_pemeliharaan_kategori', 'field_type' => 'select', 'field_label' => 'Jenis Pemeliharaan (Tanah, Peralatan dan mesin, Gedung dan Bangunan, JIJ serta Aset Tetap Lainnya)', 'is_required' => true, 'options' => ['Tanah' => 'Tanah', 'Peralatan dan mesin' => 'Peralatan dan mesin', 'Gedung dan Bangunan' => 'Gedung dan Bangunan', 'JIJ' => 'JIJ', 'Aset Tetap Lainnya' => 'Aset Tetap Lainnya'], 'order' => 10],
            ['field_name' => 'rincian_pemeliharaan', 'field_type' => 'text', 'field_label' => 'Rincian Jenis Pemeliharaan misal : Kendaraan (Plat Nomor)', 'is_required' => true, 'order' => 11],
        ];

        foreach ($fields as $field) {
            ExpenseField::create(array_merge($field, ['expense_type_id' => $typeId]));
        }
    }
}
