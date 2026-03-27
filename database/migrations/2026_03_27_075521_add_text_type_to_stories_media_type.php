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
        // Modify the enum to include 'text'
        DB::statement("ALTER TABLE stories MODIFY COLUMN media_type ENUM('image', 'video', 'text') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum
        DB::statement("ALTER TABLE stories MODIFY COLUMN media_type ENUM('image', 'video') NOT NULL");
    }
};
