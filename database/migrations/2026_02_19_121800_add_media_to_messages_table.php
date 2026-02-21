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
        Schema::table('messages', function (Blueprint $table) {
            // Modify type enum to include video
            $table->enum('type', ['text', 'image', 'video', 'file'])->default('text')->change();
            
            // Add media columns
            $table->string('media_path')->nullable()->after('type');
            $table->string('media_thumbnail')->nullable()->after('media_path');
            $table->string('original_filename')->nullable()->after('media_thumbnail');
            $table->integer('media_size')->nullable()->after('original_filename');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['media_path', 'media_thumbnail', 'original_filename', 'media_size']);
            $table->enum('type', ['text', 'image', 'file'])->default('text')->change();
        });
    }
};