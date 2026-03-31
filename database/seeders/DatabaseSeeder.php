<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->call([
            Exported\UsersSeeder::class,
            Exported\RolesSeeder::class,
            Exported\PermissionsSeeder::class,
            Exported\ModelHasRolesSeeder::class,
            Exported\RoleHasPermissionsSeeder::class,
            Exported\InstansisSeeder::class,
            Exported\InstansiUserSeeder::class,
            Exported\ProgramsSeeder::class,
            Exported\KegiatansSeeder::class,
            Exported\SubKegiatansSeeder::class,
            Exported\RekeningsSeeder::class,
            Exported\DetailBelanjasSeeder::class,
            Exported\PegawaisSeeder::class,
            Exported\ExpenseTypesSeeder::class,
            Exported\ExpenseFieldsSeeder::class,
            Exported\Sp2dsSeeder::class,
            Exported\SettingsSeeder::class,
            Exported\NotificationsSeeder::class,
            Exported\ActivityLogSeeder::class,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
