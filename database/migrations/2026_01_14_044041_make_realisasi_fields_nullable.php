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
        Schema::table('realisasis', function (Blueprint $table) {
            $table->foreignId('detail_belanja_id')->nullable()->change();
            $table->foreignId('expense_type_id')->nullable()->change();
            $table->date('tanggal_realisasi')->nullable()->change();
            $table->decimal('jumlah', 20, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasis', function (Blueprint $table) {
            $table->foreignId('detail_belanja_id')->nullable(false)->change();
            $table->foreignId('expense_type_id')->nullable(false)->change();
            $table->date('tanggal_realisasi')->nullable(false)->change();
            $table->decimal('jumlah', 20, 2)->nullable(false)->change();
        });
    }
};
