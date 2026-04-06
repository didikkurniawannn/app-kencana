<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SyncAdminInstansiPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Role::where('name', 'super_admin')->first();
        $adminInstansi = Role::firstOrCreate(['name' => 'admin_instansi']);

        if (!$superAdmin) {
            $this->command->error('Role super_admin tidak ditemukan!');
            return;
        }

        // Ambil semua permission dari super_admin
        $permissions = $superAdmin->permissions->pluck('name')->toArray();

        // Daftar permission yang HARUS DIKECUALIKAN untuk admin_instansi
        $excludedPatterns = [
            'role',           // Shield roles management
            'instansi',       // Global institutions management
            'ManageSettings', // Global system settings page
        ];

        $filteredPermissions = array_filter($permissions, function ($permissionName) use ($excludedPatterns) {
            foreach ($excludedPatterns as $pattern) {
                if (str_contains(strtolower($permissionName), strtolower($pattern))) {
                    return false;
                }
            }
            return true;
        });

        // Sinkronisasi permission ke admin_instansi
        $adminInstansi->syncPermissions($filteredPermissions);

        $this->command->info('Berhasil menyinkronkan ' . count($filteredPermissions) . ' permission ke role admin_instansi.');
    }
}
