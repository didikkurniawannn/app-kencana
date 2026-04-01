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
        Schema::table('sp2ds', function (Blueprint $table) {
            if (!Schema::hasColumn('sp2ds', 'nomor_register')) {
                $table->string('nomor_register')->nullable()->after('nomor_sp2d');
            }
            $table->string('arsip_ruang')->nullable()->after('lokasi_arsip_fisik');
            $table->string('arsip_box')->nullable()->after('arsip_ruang');
            $table->string('arsip_rak_type')->nullable()->after('arsip_box'); // 'Rak' or 'Roll o pact'
            $table->string('arsip_filing_cabinet')->nullable()->after('arsip_rak_type');
            $table->string('arsip_sampul')->nullable()->after('arsip_filing_cabinet');
        });

        Schema::table('realisasis', function (Blueprint $table) {
            if (!Schema::hasColumn('realisasis', 'nomor_register')) {
                $table->string('nomor_register')->nullable()->after('id');
            }
            $table->string('arsip_ruang')->nullable()->after('instansi_id');
            $table->string('arsip_box')->nullable()->after('arsip_ruang');
            $table->string('arsip_rak_type')->nullable()->after('arsip_box'); 
            $table->string('arsip_filing_cabinet')->nullable()->after('arsip_rak_type');
            $table->string('arsip_sampul')->nullable()->after('arsip_filing_cabinet');
            $table->string('status_arsip')->default('proses')->after('arsip_sampul');
            $table->string('kode_klasifikasi')->nullable()->after('status_arsip');
            $table->integer('masa_retensi')->default(10)->after('kode_klasifikasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sp2ds', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_register',
                'arsip_ruang',
                'arsip_box',
                'arsip_rak_type',
                'arsip_filing_cabinet',
                'arsip_sampul',
            ]);
        });

        Schema::table('realisasis', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_register',
                'arsip_ruang',
                'arsip_box',
                'arsip_rak_type',
                'arsip_filing_cabinet',
                'arsip_sampul',
                'status_arsip',
                'kode_klasifikasi',
                'masa_retensi',
            ]);
        });
    }
};
