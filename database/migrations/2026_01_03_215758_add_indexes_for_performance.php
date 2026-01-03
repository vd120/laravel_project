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
        // Add performance indexes for frequently queried columns
        // Add indexes that are most likely missing

        Schema::table('posts', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']); // For timeline queries
            $table->index('is_private'); // For filtering private posts
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->index('last_message_at'); // For ordering conversations
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['conversation_id', 'created_at']); // For message history
            $table->index(['sender_id', 'created_at']); // For user message history
            $table->index('read_at'); // For unread message queries
            $table->index('notified_at'); // For notification queries
        });

        Schema::table('follows', function (Blueprint $table) {
            $table->index('followed_id'); // For follower counts
        });

        Schema::table('likes', function (Blueprint $table) {
            $table->index(['post_id', 'created_at']); // For post like counts
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index(['post_id', 'created_at']); // For post comments
            $table->index(['user_id', 'created_at']); // For user comments
        });

        Schema::table('stories', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']); // For user stories
            $table->index('expires_at'); // For active stories
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at']); // For user notifications
            $table->index(['user_id', 'created_at']); // For recent notifications
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all added indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['email']);
            $table->dropIndex(['is_admin', 'is_suspended']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['is_private']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['user1_id', 'user2_id']);
            $table->dropIndex(['last_message_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['conversation_id', 'created_at']);
            $table->dropIndex(['sender_id', 'created_at']);
            $table->dropIndex(['read_at']);
            $table->dropIndex(['notified_at']);
        });

        Schema::table('follows', function (Blueprint $table) {
            $table->dropIndex(['follower_id', 'followed_id']);
            $table->dropIndex(['followed_id']);
        });

        Schema::table('likes', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'post_id']);
            $table->dropIndex(['post_id', 'created_at']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['post_id', 'created_at']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('stories', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['expires_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'read_at']);
            $table->dropIndex(['user_id', 'created_at']);
        });
    }
};
