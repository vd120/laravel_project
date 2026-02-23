<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify type enum to include 'system' for group system messages
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('text', 'image', 'video', 'file', 'system') DEFAULT 'text'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('text', 'image', 'video', 'file') DEFAULT 'text'");
    }
};