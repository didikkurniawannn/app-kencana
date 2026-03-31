<?php

namespace Database\Seeders\Exported;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('programs')->truncate();
        DB::table('programs')->insert([
            [
                'id' => 17,
                'tahun_anggaran' => 2026,
                'kode_program' => '07.01.01',
                'nama_program' => 'PROGRAM PENUNJANG URUSAN PEMERINTAHAN DAERAH KABUPATEN',
                'created_at' => '2026-03-30 10:13:02',
                'updated_at' => '2026-03-30 10:13:02',
                'instansi_id' => 1,
            ],
            [
                'id' => 18,
                'tahun_anggaran' => 2026,
                'kode_program' => '07.01.02',
                'nama_program' => 'PROGRAM PENYELENGGARAAN PEMERINTAHAN DAN PELAYANAN PUBLIK',
                'created_at' => '2026-03-30 10:13:02',
                'updated_at' => '2026-03-30 10:13:02',
                'instansi_id' => 1,
            ],
            [
                'id' => 19,
                'tahun_anggaran' => 2026,
                'kode_program' => '07.01.03',
                'nama_program' => 'PROGRAM PEMBERDAYAAN MASYARAKAT DESA DAN KELURAHAN',
                'created_at' => '2026-03-30 10:13:02',
                'updated_at' => '2026-03-30 10:13:02',
                'instansi_id' => 1,
            ],
            [
                'id' => 20,
                'tahun_anggaran' => 2026,
                'kode_program' => '07.01.04',
                'nama_program' => 'PROGRAM KOORDINASI KETENTRAMAN DAN KETERTIBAN UMUM',
                'created_at' => '2026-03-30 10:13:02',
                'updated_at' => '2026-03-30 10:13:02',
                'instansi_id' => 1,
            ],
            [
                'id' => 21,
                'tahun_anggaran' => 2026,
                'kode_program' => '07.01.05',
                'nama_program' => 'PROGRAM PENYELENGGARAAN URUSAN PEMERINTAHAN UMUM',
                'created_at' => '2026-03-30 10:13:02',
                'updated_at' => '2026-03-30 10:13:02',
                'instansi_id' => 1,
            ],
            [
                'id' => 22,
                'tahun_anggaran' => 2026,
                'kode_program' => '07.01.06',
                'nama_program' => 'PROGRAM PEMBINAAN DAN PENGAWASAN PEMERINTAHAN DESA',
                'created_at' => '2026-03-30 10:13:02',
                'updated_at' => '2026-03-30 10:13:02',
                'instansi_id' => 1,
            ],
        ]);
    }
}
