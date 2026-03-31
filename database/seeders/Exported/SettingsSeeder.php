<?php

namespace Database\Seeders\Exported;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->truncate();
        DB::table('settings')->insert([
            [
                'id' => 1,
                'key' => 'primary_color',
                'value' => '#14797b',
                'created_at' => '2025-12-21 05:07:54',
                'updated_at' => '2026-03-30 12:19:58',
                'instansi_id' => 1,
            ],
            [
                'id' => 2,
                'key' => 'app_name',
                'value' => 'Pemerintah Kabupaten Bandung',
                'created_at' => '2025-12-21 05:07:54',
                'updated_at' => '2025-12-21 09:45:32',
                'instansi_id' => 1,
            ],
            [
                'id' => 3,
                'key' => 'app_description',
                'value' => 'Sistem Penatausahaan Keuangan',
                'created_at' => '2025-12-21 05:10:33',
                'updated_at' => '2025-12-21 07:35:03',
                'instansi_id' => 1,
            ],
            [
                'id' => 4,
                'key' => 'app_logo',
                'value' => 'settings/01KMYVXY0BS1TGSJY1FM6TSTKM.png',
                'created_at' => '2025-12-21 05:10:33',
                'updated_at' => '2026-03-30 07:56:58',
                'instansi_id' => 1,
            ],
            [
                'id' => 5,
                'key' => 'app_favicon',
                'value' => 'settings/01KMYVYD1MWSRP3C9TW8XDZNZK.png',
                'created_at' => '2025-12-21 05:10:33',
                'updated_at' => '2026-03-30 07:57:14',
                'instansi_id' => 1,
            ],
            [
                'id' => 6,
                'key' => 'tahun_anggaran_aktif',
                'value' => '2026',
                'created_at' => '2025-12-21 05:19:39',
                'updated_at' => '2025-12-21 07:35:03',
                'instansi_id' => 1,
            ],
        ]);
    }
}
