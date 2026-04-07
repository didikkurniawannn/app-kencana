<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Menggunakan DB::statement untuk mengubah enum lebih aman di MySQL
        DB::statement("ALTER TABLE realisasis MODIFY COLUMN status ENUM('draft', 'diajukan', 'verifikasi', 'disetujui', 'ditolak', 'dikembalikan') DEFAULT 'draft' NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE realisasis MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected') DEFAULT 'draft' NOT NULL");
    }
};
