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
        $tables = [
            'expense_types',
            'kegiatans',
            'sub_kegiatans',
            'rekenings',
            'detail_belanjas',
            'expense_fields',
            'realisasi_details'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('instansi_id')->nullable()->constrained()->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'expense_types',
            'kegiatans',
            'sub_kegiatans',
            'rekenings',
            'detail_belanjas',
            'expense_fields',
            'realisasi_details'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['instansi_id']);
                $table->dropColumn('instansi_id');
            });
        }
    }
};
