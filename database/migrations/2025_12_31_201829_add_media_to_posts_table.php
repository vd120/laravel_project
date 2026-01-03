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
        Schema::table('posts', function (Blueprint $table) {
            $table->enum('media_type', ['image', 'video'])->nullable()->after('content');
            $table->string('media_path')->nullable()->after('media_type');
            $table->string('media_thumbnail')->nullable()->after('media_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'media_path', 'media_thumbnail']);
        });
    }
};
