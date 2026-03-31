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
        Schema::table('budget_revisions', function (Blueprint $table) {
            $table->decimal('kuefisien_lama', 15, 2)->after('detail_belanja_id')->nullable();
            $table->decimal('kuefisien_baru', 15, 2)->after('pagu_baru')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('budget_revisions', function (Blueprint $table) {
            $table->dropColumn(['kuefisien_lama', 'kuefisien_baru']);
        });
    }
};
