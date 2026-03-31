<?php

namespace Database\Seeders;

use App\Models\ExpenseField;
use App\Models\ExpenseType;
use Illuminate\Database\Seeder;

class PerjalananDinasFieldsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dalam Daerah
        $dalam = ExpenseType::updateOrCreate(
            ['slug' => 'pdt-dalam-daerah'],
            [
                'name' => 'Perjalanan Dinas Dalam Daerah',
                'is_active' => true
            ]
        );

        ExpenseField::where('expense_type_id', $dalam->id)->delete();

        $dalamFields = [
            ['field_label' => 'NIP', 'field_name' => 'nip', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Pangkat', 'field_name' => 'pangkat', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Golongan', 'field_name' => 'golongan', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Jabatan', 'field_name' => 'jabatan', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Nomor SP2D', 'field_name' => 'nomor_sp2d', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Tanggal SP2D', 'field_name' => 'tanggal_sp2d', 'field_type' => 'date', 'is_required' => true],
            ['field_label' => 'Nomor Surat Tugas', 'field_name' => 'no_surat_tugas', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Tanggal Surat Tugas', 'field_name' => 'tgl_surat_tugas', 'field_type' => 'date', 'is_required' => true],
            ['field_label' => 'Nomor SPPD', 'field_name' => 'no_sppd', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Tanggal SPPD', 'field_name' => 'tgl_sppd', 'field_type' => 'date', 'is_required' => true],
            ['field_label' => 'Tanggal Berangkat', 'field_name' => 'tgl_berangkat', 'field_type' => 'date', 'is_required' => true],
            ['field_label' => 'Tanggal Kembali', 'field_name' => 'tgl_kembali', 'field_type' => 'date', 'is_required' => true],
            ['field_label' => 'Jumlah Hari', 'field_name' => 'jumlah_hari', 'field_type' => 'number', 'is_required' => true],
            ['field_label' => 'Maksud Perjalanan Dinas', 'field_name' => 'maksud_pd', 'field_type' => 'textarea', 'is_required' => true],
            ['field_label' => 'Tempat Tujuan', 'field_name' => 'tujuan', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Uang Harian (Rp)', 'field_name' => 'uang_harian', 'field_type' => 'number', 'is_required' => false],
            ['field_label' => 'Representasi (Rp)', 'field_name' => 'representasi', 'field_type' => 'number', 'is_required' => false],
            ['field_label' => 'Biaya BBM', 'field_name' => 'biaya_bbm', 'field_type' => 'number', 'is_required' => false],
            ['field_label' => 'Biaya Transport Lokal', 'field_name' => 'transport_lokal', 'field_type' => 'number', 'is_required' => false],
            ['field_label' => 'Biaya Lain-lain', 'field_name' => 'biaya_lain', 'field_type' => 'number', 'is_required' => false],
            ['field_label' => 'Total Biaya SPD (Rp)', 'field_name' => 'total_biaya_spd', 'field_type' => 'number', 'is_required' => true],
        ];

        foreach ($dalamFields as $index => $field) {
            ExpenseField::create(array_merge($field, [
                'expense_type_id' => $dalam->id,
                'order' => $index,
            ]));
        }

        // 2. Luar Daerah
        $luar = ExpenseType::updateOrCreate(
            ['slug' => 'pdt-luar-daerah'],
            [
                'name' => 'Perjalanan Dinas Luar Daerah',
                'is_active' => true
            ]
        );

        ExpenseField::where('expense_type_id', $luar->id)->delete();

        $luarFields = [
            ['field_label' => 'NIP', 'field_name' => 'nip', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Pangkat', 'field_name' => 'pangkat', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Golongan', 'field_name' => 'golongan', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Jabatan', 'field_name' => 'jabatan', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Nomor SP2D', 'field_name' => 'nomor_sp2d', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Tanggal SP2D', 'field_name' => 'tanggal_sp2d', 'field_type' => 'date', 'is_required' => true],
            ['field_label' => 'Nomor Surat Tugas', 'field_name' => 'no_surat_tugas', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Tanggal Surat Tugas', 'field_name' => 'tgl_surat_tugas', 'field_type' => 'date', 'is_required' => true],
            ['field_label' => 'Nomor SPPD', 'field_name' => 'no_sppd', 'field_type' => 'text', 'is_required' => true],
            ['field_label' => 'Tanggal SPPD', 'field_name' => 'tgl_sppd', 'field_type' => 'date', 'is_required' => true],
            ['field_label' => 'Tanggal Berangkat', 'field_name' => 'tgl_berangkat', 'field_type' => 'date', 'is_required' => true],
            ['field_label' => 'Tanggal Kembali', 'field_name' => 'tgl_kembali', 'field_type' => 'date', 'is_required' => true],
            ['field_label' => 'Jumlah Hari', 'field_name' => 'jumlah_hari', 'field_type' => 'number', 'is_required' => true],
            ['field_label' => 'Maksud Perjalanan Dinas', 'field_name' => 'maksud_pd', 'field_type' => 'textarea', 'is_required' => true],
            ['field_label' => 'Tempat Tujuan', 'field_name' => 'tujuan', 'field_type' => 'text', 'is_required' => true],

            // Penginapan
            ['field_label' => 'Nama Hotel/Penginapan', 'field_name' => 'nama_hotel', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Tanggal Check In', 'field_name' => 'tgl_checkin', 'field_type' => 'date', 'is_required' => false],
            ['field_label' => 'Tanggal Check Out', 'field_name' => 'tgl_checkout', 'field_type' => 'date', 'is_required' => false],

            // Tiket Berangkat
            ['field_label' => 'Maskapai Berangkat', 'field_name' => 'maskapai_berangkat', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'No Tiket Berangkat', 'field_name' => 'no_tiket_berangkat', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Kode Booking Berangkat', 'field_name' => 'kode_booking_berangkat', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'No Penerbangan Berangkat', 'field_name' => 'no_penerbangan_berangkat', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Rute Asal Berangkat', 'field_name' => 'rute_asal_berangkat', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Rute Tujuan Berangkat', 'field_name' => 'rute_tujuan_berangkat', 'field_type' => 'text', 'is_required' => false],

            // Tiket Pulang
            ['field_label' => 'Maskapai Pulang', 'field_name' => 'maskapai_pulang', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'No Tiket Pulang', 'field_name' => 'no_tiket_pulang', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Kode Booking Pulang', 'field_name' => 'kode_booking_pulang', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'No Penerbangan Pulang', 'field_name' => 'no_penerbangan_pulang', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Rute Asal Pulang', 'field_name' => 'rute_asal_pulang', 'field_type' => 'text', 'is_required' => false],
            ['field_label' => 'Rute Tujuan Pulang', 'field_name' => 'rute_tujuan_pulang', 'field_type' => 'text', 'is_required' => false],

            ['field_label' => 'Uang Harian (Rp)', 'field_name' => 'uang_harian', 'field_type' => 'number', 'is_required' => false],
            ['field_label' => 'Penginapan/ Hotel (Rp)', 'field_name' => 'biaya_hotel', 'field_type' => 'number', 'is_required' => false],
            ['field_label' => 'Tiket Pesawat Berangkat (Rp)', 'field_name' => 'biaya_tiket_pergi', 'field_type' => 'number', 'is_required' => false],
            ['field_label' => 'Tiket Pesawat Pulang (Rp)', 'field_name' => 'biaya_tiket_pulang', 'field_type' => 'number', 'is_required' => false],
            ['field_label' => 'Taksi/Sewa Mobil/KA (Rp)', 'field_name' => 'biaya_transport_luar', 'field_type' => 'number', 'is_required' => false],
            ['field_label' => 'Biaya BBM', 'field_name' => 'biaya_bbm', 'field_type' => 'number', 'is_required' => false],
            ['field_label' => 'Biaya Lain-lain/Tol', 'field_name' => 'biaya_lain', 'field_type' => 'number', 'is_required' => false],
            ['field_label' => 'Total Biaya SPD (Rp)', 'field_name' => 'total_biaya_spd', 'field_type' => 'number', 'is_required' => true],
        ];

        foreach ($luarFields as $index => $field) {
            ExpenseField::create(array_merge($field, [
                'expense_type_id' => $luar->id,
                'order' => $index,
            ]));
        }

        // Remove old generic "Perjalanan Dinas" if exists
        ExpenseType::where('id', 2)->delete();
    }
}
