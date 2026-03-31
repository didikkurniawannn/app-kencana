<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('detail_belanjas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekening_id')->constrained('rekenings')->cascadeOnDelete();
            $table->text('nama_detail_belanja');
            $table->decimal('kuefisien', 15, 2)->default(0);
            $table->string('satuan')->nullable();
            $table->decimal('harga', 15, 2)->default(0);
            $table->decimal('pagu', 20, 2)->default(0);
            $table->decimal('realisasi_total', 20, 2)->default(0);
            $table->decimal('sisa_pagu', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_belanjas');
    }
};
