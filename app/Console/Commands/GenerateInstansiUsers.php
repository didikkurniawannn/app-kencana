<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Instansi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GenerateInstansiUsers extends Command
{
    protected $signature = 'app:generate-instansi-users {instansi_id?}';
    protected $description = 'Generate default users for all or specific instansi';

    public function handle()
    {
        $instansiId = $this->argument('instansi_id');
        $instansis = $instansiId ? Instansi::where('id', $instansiId)->get() : Instansi::all();

        if ($instansis->isEmpty()) {
            $this->error('No instansi found.');
            return;
        }

        $roles = ['admin_instansi', 'operator', 'verifikator', 'pimpinan', 'bendahara'];
        $password = 'bedas2026';

        foreach ($instansis as $instansi) {
            $this->info("Generating users for: {$instansi->name}");
            
            // Clean code from dots
            $cleanCode = str_replace('.', '', $instansi->kode);

            foreach ($roles as $role) {
                $roleLabel = str_replace('_', ' ', $role);
                $email = "{$cleanCode}.{$role}@kencana.id";
                $userName = $instansi->name . ' - ' . ucwords($roleLabel);

                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $userName,
                        'password' => $password, // Hashed by model cast
                        'phone_number' => null,
                    ]
                );

                // Sync Role
                $user->syncRoles([$role]);

                // Sync Tenant (Instansi)
                $user->instansi()->syncWithoutDetaching([$instansi->id]);

                $this->line(" - Created user: {$email} with role {$role}");
            }
        }

        $this->info('Done! All default users have been generated.');
    }
}
