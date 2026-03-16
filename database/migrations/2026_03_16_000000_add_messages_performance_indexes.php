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
            // Composite index for conversation messages with ordering
            $table->index(['conversation_id', 'created_at', 'deleted_at']);
            
            // Index for deleted_for JSON queries
            $table->index('deleted_for');
            
            // Index for visible_to filtering
            $table->index('visible_to');
            
            // Index for deleted_by_sender flag
            $table->index('deleted_by_sender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['conversation_id', 'created_at', 'deleted_at']);
            $table->dropIndex('deleted_for');
            $table->dropIndex('visible_to');
            $table->dropIndex('deleted_by_sender');
        });
    }
};
