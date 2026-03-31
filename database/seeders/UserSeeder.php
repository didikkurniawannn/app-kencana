<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $operatorRole = Role::firstOrCreate(['name' => 'operator']);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@cangkuang.go.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($adminRole);

        // Create operator user
        $operator = User::firstOrCreate(
            ['email' => 'operator@cangkuang.go.id'],
            [
                'name' => 'Operator Keuangan',
                'password' => Hash::make('password'),
            ]
        );
        $operator->assignRole($operatorRole);
    }
}
