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
            $table->string('lokasi_arsip_fisik')->nullable()->after('keterangan');
            $table->string('status_arsip')->default('proses')->after('lokasi_arsip_fisik'); // proses, lengkap, diarsipkan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sp2ds', function (Blueprint $table) {
            $table->dropColumn(['lokasi_arsip_fisik', 'status_arsip']);
        });
    }
};
