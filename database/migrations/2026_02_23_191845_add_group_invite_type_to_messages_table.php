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
        // Modify the enum column to include new types
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('text', 'image', 'video', 'file', 'system', 'group_invite') DEFAULT 'text'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('text', 'image', 'file') DEFAULT 'text'");
    }
};