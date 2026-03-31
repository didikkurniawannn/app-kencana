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
            if (!Schema::hasColumn('sp2ds', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sisa_jumlah');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sp2ds', function (Blueprint $table) {
            if (Schema::hasColumn('sp2ds', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
