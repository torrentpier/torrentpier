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
        Schema::create('word_filters', function (Blueprint $table) {
            $table->id();
            $table->string('pattern');
            $table->string('replacement')->nullable();
            $table->enum('filter_type', ['replace', 'block', 'moderate']);
            $table->enum('pattern_type', ['exact', 'wildcard', 'regex']);
            $table->enum('severity', ['low', 'medium', 'high']);
            $table->boolean('is_active')->default(true);
            $table->boolean('case_sensitive')->default(false);
            $table->json('applies_to');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('pattern');
            $table->index('filter_type');
            $table->index('is_active');
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('word_filters');
    }
};
