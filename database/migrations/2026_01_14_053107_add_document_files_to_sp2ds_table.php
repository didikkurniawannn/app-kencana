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
            $table->string('file_spm')->nullable()->after('keterangan');
            $table->string('file_spp')->nullable()->after('file_spm');
            $table->string('file_sp2d')->nullable()->after('file_spp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sp2ds', function (Blueprint $table) {
            $table->dropColumn(['file_spm', 'file_spp', 'file_sp2d']);
        });
    }
};
