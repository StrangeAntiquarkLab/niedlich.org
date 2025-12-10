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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // canonical tag name
            // Whitelist for media types the tag belongs to. If none are set (null) all media types are allowed to receive that tag
            $table->set('whitelist', ['text', 'image', 'gif', 'webm', 'mp4', 'fact'])->nullable();
            $table->timestamps();
        });

        Schema::create('tag_synonyms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->references('id')->on('tags')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('synonym')->unique(); // 1:n Tags:Synonyms
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags_synonyms');
        Schema::dropIfExists('tags');
    }
};
