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
        Schema::table('realisasis', function (Blueprint $table) {
            $table->dropForeign('realisasis_detail_belanja_id_foreign');
            $table->foreign('detail_belanja_id')->references('id')->on('detail_belanjas')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasis', function (Blueprint $table) {
            $table->dropForeign('realisasis_detail_belanja_id_foreign');
            $table->foreign('detail_belanja_id')->references('id')->on('detail_belanjas')->cascadeOnDelete();
        });
    }
};
