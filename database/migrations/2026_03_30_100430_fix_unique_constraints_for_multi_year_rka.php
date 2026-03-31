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
        Schema::table('programs', function (Blueprint $table) {
            $table->dropUnique('programs_kode_program_unique');
            $table->unique(['kode_program', 'tahun_anggaran', 'instansi_id'], 'programs_kode_tahun_instansi_unique');
        });

        Schema::table('kegiatans', function (Blueprint $table) {
            $table->dropUnique('kegiatans_kode_kegiatan_unique');
            $table->unique(['kode_kegiatan', 'program_id'], 'kegiatans_kode_program_unique');
        });

        Schema::table('sub_kegiatans', function (Blueprint $table) {
            $table->dropUnique('sub_kegiatans_kode_sub_kegiatan_unique');
            $table->unique(['kode_sub_kegiatan', 'kegiatan_id'], 'sub_keg_kode_keg_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_kegiatans', function (Blueprint $table) {
            $table->dropUnique('sub_keg_kode_keg_unique');
            $table->unique('kode_sub_kegiatan', 'sub_kegiatans_kode_sub_kegiatan_unique');
        });

        Schema::table('kegiatans', function (Blueprint $table) {
            $table->dropUnique('kegiatans_kode_program_unique');
            $table->unique('kode_kegiatan', 'kegiatans_kode_kegiatan_unique');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropUnique('programs_kode_tahun_instansi_unique');
            $table->unique('kode_program', 'programs_kode_program_unique');
        });
    }
};
