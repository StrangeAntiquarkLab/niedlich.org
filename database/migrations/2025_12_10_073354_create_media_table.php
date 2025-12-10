<?php

use Illuminate\Support\Facades\Auth;
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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('species_id')->nullable()->constrained('species')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('creator')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();

            $table->enum('type', ['pic', 'gif', 'webm', 'mp4'])->default('pic');

            // Cloudflare Image Information
            $table->string('cf_id')->unique()->nullable(); // Cloudflare Image ID
            $table->string('url')->nullable(); // Cached/Fallback URL, will be preferred if set

            $table->text('description')->nullable();
            $table->text('source')->nullable();

            $table->timestamps();
        });

        // Media and Tags have a n:n relationship
        Schema::create('media_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_tags');
        Schema::dropIfExists('media');
    }
};
