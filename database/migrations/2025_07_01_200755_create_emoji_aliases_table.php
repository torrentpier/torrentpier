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
        Schema::create('emoji_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emoji_id')->constrained('emojis')->cascadeOnDelete();
            $table->string('alias')->unique();
            $table->timestamps();

            // Composite index for performance when joining
            $table->index(['emoji_id', 'alias']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emoji_aliases');
    }
};
