<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->boolean('is_assignable')->default(true); // Set to false for parent species that should not be assignable.
                                                             // Used for things like "Feline" where the exact species should be assigned.
            $table->timestamps();
        });

        Schema::table('species', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('species')->cascadeOnUpdate()->nullOnDelete();;
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('species');
    }
};
