<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            // Add a mime_type column after the type column, because types still can have multiple MIME types
            $table->enum('mime_type', [
                'image/gif',
                'video/webm',
                'video/mp4',
                'image/jpeg',
                'image/png',
                'image/webp'
            ])->after('type');
        });

        // Update the mime_type column for existing media, where type is known
        DB::table('media')->where('type', 'gif')->update(['mime_type' => 'image/gif']);
        DB::table('media')->where('type', 'webm')->update(['mime_type' => 'video/webm']);
        DB::table('media')->where('type', 'mp4')->update(['mime_type' => 'video/mp4']);

        // Not perfect, but we currently can't allow it to be null
        DB::table('media')->where('type', 'pic')->update(['mime_type' => 'image/jpeg']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('mime_type');
        });
    }
};
