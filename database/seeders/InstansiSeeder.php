<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Instansi;
use App\Models\User;
use Illuminate\Support\Str;

class InstansiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kecamatans = [
            ['kode' => '32.04.05', 'name' => 'Ciwidey'],
            ['kode' => '32.04.06', 'name' => 'Rancabali'],
            ['kode' => '32.04.07', 'name' => 'Pasirjambu'],
            ['kode' => '32.04.08', 'name' => 'Cimaung'],
            ['kode' => '32.04.09', 'name' => 'Pangalengan'],
            ['kode' => '32.04.10', 'name' => 'Kertasari'],
            ['kode' => '32.04.11', 'name' => 'Pacet'],
            ['kode' => '32.04.12', 'name' => 'Ciparay'],
            ['kode' => '32.04.13', 'name' => 'Baleendah'],
            ['kode' => '32.04.14', 'name' => 'Arjasari'],
            ['kode' => '32.04.15', 'name' => 'Banjaran'],
            ['kode' => '32.04.16', 'name' => 'Cangkuang'],
            ['kode' => '32.04.17', 'name' => 'Pameungpeuk'],
            ['kode' => '32.04.18', 'name' => 'Katapang'],
            ['kode' => '32.04.19', 'name' => 'Soreang'],
            ['kode' => '32.04.20', 'name' => 'Kutawaringin'],
            ['kode' => '32.04.25', 'name' => 'Margaasih'],
            ['kode' => '32.04.26', 'name' => 'Margahayu'],
            ['kode' => '32.04.27', 'name' => 'Dayeuhkolot'],
            ['kode' => '32.04.28', 'name' => 'Bojongsoang'],
            ['kode' => '32.04.29', 'name' => 'Cileunyi'],
            ['kode' => '32.04.30', 'name' => 'Cilengkrang'],
            ['kode' => '32.04.31', 'name' => 'Cimenyan'],
            // ID 24 as per spread-sheet had duplicate name 'Bojongsoang' with id 32.04.32 but we will keep as is
            ['kode' => '32.04.32', 'name' => 'Bojongsoang (2)'],
            ['kode' => '32.04.33', 'name' => 'Rancaekek'],
            ['kode' => '32.04.34', 'name' => 'Nagreg'],
            ['kode' => '32.04.35', 'name' => 'Cicalengka'],
            ['kode' => '32.04.36', 'name' => 'Cikancung'], // Typo in sheet fixed 'Chikancung' -> 'Cikancung'
            ['kode' => '32.04.37', 'name' => 'Majalaya'],
            ['kode' => '32.04.38', 'name' => 'Solokanjeruk'],
            ['kode' => '32.04.44', 'name' => 'Ibun'],
        ];

        $insertedIds = [];

        foreach ($kecamatans as $kecamatan) {
            $instansi = Instansi::updateOrCreate(
                ['slug' => Str::slug($kecamatan['name'])],
                [
                    'kode' => $kecamatan['kode'],
                    'name' => $kecamatan['name'],
                    'primary_color' => '#14797b' // default emerald kencana
                ]
            );
            $insertedIds[] = $instansi->id;
        }

        // Attach super admins to these new tenants so they dont get locked out
        $superAdmins = User::whereHas('roles', fn($query) => $query->where('name', 'super_admin'))->get();
        if ($superAdmins->isNotEmpty()) {
            foreach ($superAdmins as $superAdmin) {
                // syncWithoutDetaching will add these new Instansis to the superadmin's access list 
                // without removing existing accesses if any.
                $superAdmin->instansi()->syncWithoutDetaching($insertedIds);
            }
        }
    }
}
