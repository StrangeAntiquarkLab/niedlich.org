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
        Schema::create('facts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('species_id')->nullable()->constrained('species')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('tag_id')->nullable()->constrained('tags')->cascadeOnUpdate()->nullOnDelete();  // Which tag should the random image for the fact have
            $table->foreignId('creator')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('title');
            $table->text('fact');
            $table->timestamps();
        });

        // Facts and Tags have a n:n relationship
        Schema::create('fact_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fact_id')->constrained('facts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fact_tags');
        Schema::dropIfExists('facts');
    }
};
