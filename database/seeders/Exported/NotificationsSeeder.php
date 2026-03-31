<?php

namespace Database\Seeders\Exported;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('notifications')->truncate();
        DB::table('notifications')->insert([
            [
                'id' => '17dbac1f-cfb8-4028-a0c9-a1da318ec4bf',
                'type' => 'Filament\\Notifications\\DatabaseNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => 13,
                'data' => '{"actions":[],"body":"Sumber Dana SPM: 3204\\/03.0\\/000004\\/UP\\/7.01.0.00.0.00.30.0000\\/M\\/1\\/2026 telah divalidasi oleh Verifikator.","color":null,"duration":"persistent","icon":"heroicon-o-document-check","iconColor":"info","status":"info","title":"Laporan Sumber Dana Selesai Diverifikasi","view":"filament-notifications::notification","viewData":[],"format":"filament"}',
                'read_at' => NULL,
                'created_at' => '2026-03-31 08:04:25',
                'updated_at' => '2026-03-31 08:04:25',
            ],
            [
                'id' => '41e96aa6-2ad0-441c-bb08-7134a2842b57',
                'type' => 'Filament\\Notifications\\DatabaseNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => 17,
                'data' => '{"actions":[],"body":"Sumber Dana SPM: 3204\\/03.0\\/000004\\/UP\\/7.01.0.00.0.00.30.0000\\/M\\/1\\/2026 telah divalidasi oleh Verifikator.","color":null,"duration":"persistent","icon":"heroicon-o-document-check","iconColor":"info","status":"info","title":"Laporan Sumber Dana Selesai Diverifikasi","view":"filament-notifications::notification","viewData":[],"format":"filament"}',
                'read_at' => NULL,
                'created_at' => '2026-03-31 08:04:25',
                'updated_at' => '2026-03-31 08:04:25',
            ],
            [
                'id' => '7e7801c2-a482-449d-b5f8-646e9bfb2773',
                'type' => 'Filament\\Notifications\\DatabaseNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => 15,
                'data' => '{"actions":[],"body":"Sumber Dana dengan nomor SPM: 32.04\\/03.0\\/000001\\/LS\\/7.01.0.00.0.00.30.0000\\/M\\/1\\/2026 senilai Rp 75.753.271 telah diverifikasi dan siap digunakan.","color":null,"duration":"persistent","icon":"heroicon-o-check-circle","iconColor":"success","status":"success","title":"Sumber Dana Terverifikasi","view":"filament-notifications::notification","viewData":[],"format":"filament"}',
                'read_at' => NULL,
                'created_at' => '2026-03-31 09:25:15',
                'updated_at' => '2026-03-31 09:25:15',
            ],
            [
                'id' => 'a7d50796-a7c5-4013-b5cc-0a4abe99154f',
                'type' => 'Filament\\Notifications\\DatabaseNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => 17,
                'data' => '{"actions":[],"body":"Sumber Dana SPM: 32.04\\/03.0\\/000001\\/LS\\/7.01.0.00.0.00.30.0000\\/M\\/1\\/2026 senilai Rp 75.753.271 telah divalidasi oleh Verifikator.","color":null,"duration":"persistent","icon":"heroicon-o-document-check","iconColor":"info","status":"info","title":"Laporan Sumber Dana Selesai Diverifikasi","view":"filament-notifications::notification","viewData":[],"format":"filament"}',
                'read_at' => NULL,
                'created_at' => '2026-03-31 09:25:15',
                'updated_at' => '2026-03-31 09:25:15',
            ],
            [
                'id' => 'b62eb3e0-8542-4577-8fac-1318105c7eaf',
                'type' => 'Filament\\Notifications\\DatabaseNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => 13,
                'data' => '{"actions":[],"body":"Sumber Dana SPM: 32.04\\/03.0\\/000001\\/LS\\/7.01.0.00.0.00.30.0000\\/M\\/1\\/2026 senilai Rp 75.753.271 telah divalidasi oleh Verifikator.","color":null,"duration":"persistent","icon":"heroicon-o-document-check","iconColor":"info","status":"info","title":"Laporan Sumber Dana Selesai Diverifikasi","view":"filament-notifications::notification","viewData":[],"format":"filament"}',
                'read_at' => NULL,
                'created_at' => '2026-03-31 09:25:15',
                'updated_at' => '2026-03-31 09:25:15',
            ],
            [
                'id' => 'bf427d88-b08b-43de-9a50-df65a8614576',
                'type' => 'Filament\\Notifications\\DatabaseNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => 11,
                'data' => '{"actions":[],"body":"Sumber Dana dengan nomor SPM: 3204\\/03.0\\/000004\\/UP\\/7.01.0.00.0.00.30.0000\\/M\\/1\\/2026 telah diverifikasi dan siap digunakan.","color":null,"duration":"persistent","icon":"heroicon-o-check-circle","iconColor":"success","status":"success","title":"Sumber Dana Terverifikasi","view":"filament-notifications::notification","viewData":[],"format":"filament"}',
                'read_at' => '2026-03-31 08:07:05',
                'created_at' => '2026-03-31 08:04:25',
                'updated_at' => '2026-03-31 08:07:05',
            ],
            [
                'id' => 'ce8c9092-6c0e-4d63-b1d6-c60b5f40833d',
                'type' => 'Filament\\Notifications\\DatabaseNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => 15,
                'data' => '{"actions":[],"body":"Sumber Dana dengan nomor SPM: 3204\\/03.0\\/000004\\/UP\\/7.01.0.00.0.00.30.0000\\/M\\/1\\/2026 telah diverifikasi dan siap digunakan.","color":null,"duration":"persistent","icon":"heroicon-o-check-circle","iconColor":"success","status":"success","title":"Sumber Dana Terverifikasi","view":"filament-notifications::notification","viewData":[],"format":"filament"}',
                'read_at' => NULL,
                'created_at' => '2026-03-31 08:04:25',
                'updated_at' => '2026-03-31 08:04:25',
            ],
            [
                'id' => 'e66ce5ed-0002-4317-9bf4-cecc11e04c4b',
                'type' => 'Filament\\Notifications\\DatabaseNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => 11,
                'data' => '{"actions":[],"body":"Sumber Dana dengan nomor SPM: 32.04\\/03.0\\/000001\\/LS\\/7.01.0.00.0.00.30.0000\\/M\\/1\\/2026 senilai Rp 75.753.271 telah diverifikasi dan siap digunakan.","color":null,"duration":"persistent","icon":"heroicon-o-check-circle","iconColor":"success","status":"success","title":"Sumber Dana Terverifikasi","view":"filament-notifications::notification","viewData":[],"format":"filament"}',
                'read_at' => NULL,
                'created_at' => '2026-03-31 09:25:15',
                'updated_at' => '2026-03-31 09:25:15',
            ],
        ]);
    }
}
