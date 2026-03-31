<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sp2ds', function (Blueprint $table) {
            $table->string('kode_klasifikasi')->default('KU.01')->after('status_arsip');
            $table->integer('masa_retensi')->default(10)->after('kode_klasifikasi'); // years
            $table->string('tingkat_perkembangan')->default('Asli')->after('masa_retensi'); // Asli, Tembusan, Fotokopi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sp2ds', function (Blueprint $table) {
            $table->dropColumn(['kode_klasifikasi', 'masa_retensi', 'tingkat_perkembangan']);
        });
    }
};
