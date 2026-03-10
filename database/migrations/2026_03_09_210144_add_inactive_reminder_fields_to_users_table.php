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
        Schema::table('users', function (Blueprint $table) {
            // Track when we last sent an inactive reminder to avoid spam
            // Note: last_active already exists in the users table
            $table->timestamp('inactive_reminder_sent_at')->nullable()->after('last_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_active', 'inactive_reminder_sent_at']);
        });
    }
};
