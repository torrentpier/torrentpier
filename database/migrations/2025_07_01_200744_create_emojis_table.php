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
        Schema::create('emojis', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('emoji_text')->nullable();
            $table->string('emoji_shortcode')->unique();
            $table->string('image_url')->nullable();
            $table->boolean('sprite_mode')->default(false);
            $table->json('sprite_params')->nullable();
            $table->foreignId('emoji_category_id')->nullable()->constrained('emoji_categories')->nullOnDelete();
            $table->integer('display_order')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emojis');
    }
};
