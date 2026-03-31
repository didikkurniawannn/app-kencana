<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rekenings', function (Blueprint $table) {
            $table->id();
            $table->string('kode_rekening');
            $table->string('nama_rekening');
            $table->foreignId('sub_kegiatan_id')->constrained('sub_kegiatans')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['kode_rekening', 'sub_kegiatan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekenings');
    }
};
