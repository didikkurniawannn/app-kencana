<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('realisasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_belanja_id')->constrained('detail_belanjas')->cascadeOnDelete();
            $table->foreignId('expense_type_id')->constrained('expense_types');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('pegawai_id')->nullable()->constrained('pegawais')->nullOnDelete();
            $table->date('tanggal_realisasi');
            $table->decimal('jumlah', 20, 2);
            $table->text('keterangan')->nullable();
            $table->string('bukti_file')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasis');
    }
};
