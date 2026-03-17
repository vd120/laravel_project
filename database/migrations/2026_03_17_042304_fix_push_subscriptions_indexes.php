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
        // Drop the problematic composite index if it exists
        try {
            DB::statement('ALTER TABLE push_subscriptions DROP INDEX push_subscriptions_user_id_endpoint_index');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }

        // Add separate indexes
        try {
            DB::statement('ALTER TABLE push_subscriptions ADD INDEX user_id (user_id)');
        } catch (\Exception $e) {
            // Index already exists
        }

        try {
            DB::statement('ALTER TABLE push_subscriptions ADD INDEX endpoint (endpoint(255))');
        } catch (\Exception $e) {
            // Index already exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE push_subscriptions DROP INDEX user_id');
            DB::statement('ALTER TABLE push_subscriptions DROP INDEX endpoint');
        } catch (\Exception $e) {
            // Ignore errors on rollback
        }
    }
};
