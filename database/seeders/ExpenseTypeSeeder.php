<?php

namespace Database\Seeders;

use App\Models\ExpenseType;
use App\Models\ExpenseField;
use Illuminate\Database\Seeder;

class ExpenseTypeSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Makan dan Minum
        $makanMinum = ExpenseType::create([
            'name' => 'Makan dan Minum',
            'slug' => 'makan-minum',
            'description' => 'Belanja untuk keperluan makan dan minum kegiatan',
            'is_active' => true,
        ]);

        $makanMinumFields = [
            ['field_name' => 'nama_kegiatan', 'field_type' => 'text', 'field_label' => 'Nama Kegiatan', 'is_required' => true, 'order' => 1],
            ['field_name' => 'tanggal_pelaksanaan', 'field_type' => 'date', 'field_label' => 'Tanggal Pelaksanaan', 'is_required' => true, 'order' => 2],
            ['field_name' => 'jumlah_orang', 'field_type' => 'number', 'field_label' => 'Jumlah Orang', 'is_required' => true, 'order' => 3],
            ['field_name' => 'jenis_menu', 'field_type' => 'text', 'field_label' => 'Jenis Menu', 'is_required' => false, 'order' => 4],
            ['field_name' => 'lokasi', 'field_type' => 'text', 'field_label' => 'Lokasi', 'is_required' => false, 'order' => 5],
        ];

        foreach ($makanMinumFields as $field) {
            $makanMinum->fields()->create($field);
        }

        // 2. Perjalanan Dinas
        $perdinss = ExpenseType::create([
            'name' => 'Perjalanan Dinas',
            'slug' => 'perjalanan-dinas',
            'description' => 'Belanja untuk perjalanan dinas pegawai',
            'is_active' => true,
        ]);

        $perdinFields = [
            ['field_name' => 'tujuan', 'field_type' => 'text', 'field_label' => 'Tujuan Perjalanan', 'is_required' => true, 'order' => 1],
            ['field_name' => 'tanggal_berangkat', 'field_type' => 'date', 'field_label' => 'Tanggal Berangkat', 'is_required' => true, 'order' => 2],
            ['field_name' => 'tanggal_kembali', 'field_type' => 'date', 'field_label' => 'Tanggal Kembali', 'is_required' => true, 'order' => 3],
            ['field_name' => 'uang_harian', 'field_type' => 'number', 'field_label' => 'Uang Harian (Rp)', 'is_required' => false, 'order' => 4],
            ['field_name' => 'transport', 'field_type' => 'number', 'field_label' => 'Biaya Transport (Rp)', 'is_required' => false, 'order' => 5],
            ['field_name' => 'akomodasi', 'field_type' => 'number', 'field_label' => 'Biaya Akomodasi (Rp)', 'is_required' => false, 'order' => 6],
            ['field_name' => 'keperluan', 'field_type' => 'textarea', 'field_label' => 'Keperluan/Tujuan Dinas', 'is_required' => false, 'order' => 7],
        ];

        foreach ($perdinFields as $field) {
            $perdinss->fields()->create($field);
        }

        // 3. Pemeliharaan
        $pemeliharaan = ExpenseType::create([
            'name' => 'Pemeliharaan',
            'slug' => 'pemeliharaan',
            'description' => 'Belanja untuk pemeliharaan kendaraan, gedung, dan aset lainnya',
            'is_active' => true,
        ]);

        $pemeliharaanFields = [
            ['field_name' => 'jenis_pemeliharaan', 'field_type' => 'text', 'field_label' => 'Jenis Pemeliharaan', 'is_required' => true, 'order' => 1],
            ['field_name' => 'nama_vendor', 'field_type' => 'text', 'field_label' => 'Nama Vendor/Bengkel', 'is_required' => true, 'order' => 2],
            ['field_name' => 'no_kendaraan_aset', 'field_type' => 'text', 'field_label' => 'No Kendaraan/Kode Aset', 'is_required' => false, 'order' => 3],
            ['field_name' => 'tanggal_pemeliharaan', 'field_type' => 'date', 'field_label' => 'Tanggal Pemeliharaan', 'is_required' => true, 'order' => 4],
            ['field_name' => 'deskripsi_pekerjaan', 'field_type' => 'textarea', 'field_label' => 'Deskripsi Pekerjaan', 'is_required' => false, 'order' => 5],
        ];

        foreach ($pemeliharaanFields as $field) {
            $pemeliharaan->fields()->create($field);
        }
    }
}
