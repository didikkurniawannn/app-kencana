<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expense_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_type_id')->constrained('expense_types')->cascadeOnDelete();
            $table->string('field_name');
            $table->enum('field_type', ['text', 'number', 'date', 'select', 'file', 'textarea']);
            $table->string('field_label');
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable(); // For select field options
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_fields');
    }
};
