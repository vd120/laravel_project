<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add language column to users table for storing user's preferred language.
     * This allows users to have their language preference persisted across sessions.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add language column after email for logical grouping
            $table->string('language', 10)->default('en')->after('email')
                  ->comment('User preferred language locale (en, ar, etc.)');
            
            // Add index for faster lookups when filtering by language
            $table->index('language', 'users_language_index');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Remove the language column from users table.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the index first
            $table->dropIndex('users_language_index');
            
            // Then drop the column
            $table->dropColumn('language');
        });
    }
};
