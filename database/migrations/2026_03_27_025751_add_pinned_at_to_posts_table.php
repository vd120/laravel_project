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
            // Add pinned_at timestamp for pinning posts to top of profile
            $table->timestamp('pinned_at')->nullable()->after('is_private');
            
            // Add index for performance when querying pinned posts
            $table->index('pinned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['pinned_at']);
            $table->dropColumn('pinned_at');
        });
    }
};
