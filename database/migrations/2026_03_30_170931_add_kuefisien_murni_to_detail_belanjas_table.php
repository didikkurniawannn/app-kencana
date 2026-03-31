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
        Schema::table('detail_belanjas', function (Blueprint $table) {
            $table->decimal('kuefisien_murni', 15, 2)->after('kuefisien')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('detail_belanjas', function (Blueprint $table) {
            $table->dropColumn('kuefisien_murni');
        });
    }
};
