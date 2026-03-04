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
            // Store user IDs who deleted this message for themselves only
            $table->json('deleted_for')->nullable()->after('deleted_at');
            // Track if sender deleted for everyone
            $table->boolean('deleted_by_sender')->default(false)->after('deleted_for');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['deleted_for', 'deleted_by_sender']);
        });
    }
};
