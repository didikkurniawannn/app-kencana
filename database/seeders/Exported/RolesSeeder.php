<?php

namespace Database\Seeders\Exported;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->truncate();
        DB::table('roles')->insert([
            [
                'id' => 1,
                'name' => 'super_admin',
                'guard_name' => 'web',
                'created_at' => '2025-12-19 12:54:55',
                'updated_at' => '2025-12-19 12:54:55',
            ],
            [
                'id' => 2,
                'name' => 'operator',
                'guard_name' => 'web',
                'created_at' => '2025-12-19 12:54:55',
                'updated_at' => '2025-12-19 12:54:55',
            ],
            [
                'id' => 7,
                'name' => 'bendahara',
                'guard_name' => 'web',
                'created_at' => '2026-03-30 10:36:13',
                'updated_at' => '2026-03-30 10:36:13',
            ],
            [
                'id' => 10,
                'name' => 'verifikator',
                'guard_name' => 'web',
                'created_at' => '2026-03-30 11:43:45',
                'updated_at' => '2026-03-30 11:43:45',
            ],
            [
                'id' => 11,
                'name' => 'pimpinan',
                'guard_name' => 'web',
                'created_at' => '2026-03-30 11:43:45',
                'updated_at' => '2026-03-30 11:43:45',
            ],
            [
                'id' => 12,
                'name' => 'admin_instansi',
                'guard_name' => 'web',
                'created_at' => '2026-03-30 11:43:45',
                'updated_at' => '2026-03-30 11:43:45',
            ],
        ]);
    }
}
