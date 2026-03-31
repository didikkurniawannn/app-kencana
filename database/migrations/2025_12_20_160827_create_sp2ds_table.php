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
        Schema::create('sp2ds', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_sp2d')->unique();
            $table->date('tanggal_sp2d');
            $table->decimal('jumlah_sp2d', 15, 2);
            $table->string('sumber_dana'); // GU, LS, etc.
            $table->decimal('sisa_jumlah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sp2ds');
    }
};
