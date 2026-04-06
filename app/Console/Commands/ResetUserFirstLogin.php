<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ResetUserFirstLogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-login {email? : Email dari user yang ingin di-reset khusus} {--all : Reset seluruh user secara massal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mereset kolom profile_updated_at agar user diwajibkan mengubah profil kembali saat login';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $all = $this->option('all');

        if ($all) {
            if ($this->confirm('Apakah Anda yakin ingin me-reset SEMUA user? Mereka semua akan diwajibkan update profil lagi.')) {
                $count = User::query()->update(['profile_updated_at' => null]);
                $this->info("Berhasil me-reset {$count} user.");
            } else {
                $this->warn('Operasi dibatalkan.');
            }
            return;
        }

        if ($email) {
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("User dengan email '{$email}' tidak ditemukan.");
                return;
            }

            $user->update(['profile_updated_at' => null]);
            $this->info("Berhasil me-reset akun: {$user->name} ({$user->email}). Modal akan muncul pada login berikutnya.");
            return;
        }

        $this->warn('Harap masukkan email user, contoh: php artisan user:reset-login email@domain.com');
        $this->warn('Atau gunakan --all untuk mereset seluruh user: php artisan user:reset-login --all');
    }
}
