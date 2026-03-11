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
        // Posts table indexes
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasIndex('posts', 'posts_user_id_index')) {
                $table->index('user_id', 'posts_user_id_index');
            }
            if (!Schema::hasIndex('posts', 'posts_is_private_index')) {
                $table->index('is_private', 'posts_is_private_index');
            }
            if (!Schema::hasIndex('posts', 'posts_created_at_index')) {
                $table->index('created_at', 'posts_created_at_index');
            }
        });

        // Follows table indexes
        Schema::table('follows', function (Blueprint $table) {
            if (!Schema::hasIndex('follows', 'follows_follower_id_index')) {
                $table->index('follower_id', 'follows_follower_id_index');
            }
            if (!Schema::hasIndex('follows', 'follows_followed_id_index')) {
                $table->index('followed_id', 'follows_followed_id_index');
            }
        });

        // Stories table indexes
        Schema::table('stories', function (Blueprint $table) {
            if (!Schema::hasIndex('stories', 'stories_user_id_index')) {
                $table->index('user_id', 'stories_user_id_index');
            }
            if (!Schema::hasIndex('stories', 'stories_expires_at_index')) {
                $table->index('expires_at', 'stories_expires_at_index');
            }
        });

        // Comments table indexes
        Schema::table('comments', function (Blueprint $table) {
            if (!Schema::hasIndex('comments', 'comments_post_id_index')) {
                $table->index('post_id', 'comments_post_id_index');
            }
            if (!Schema::hasIndex('comments', 'comments_user_id_index')) {
                $table->index('user_id', 'comments_user_id_index');
            }
        });

        // Likes table indexes
        Schema::table('likes', function (Blueprint $table) {
            if (!Schema::hasIndex('likes', 'likes_post_id_index')) {
                $table->index('post_id', 'likes_post_id_index');
            }
            if (!Schema::hasIndex('likes', 'likes_user_id_index')) {
                $table->index('user_id', 'likes_user_id_index');
            }
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasIndex('users', 'users_is_online_index')) {
                $table->index('is_online', 'users_is_online_index');
            }
            if (!Schema::hasIndex('users', 'users_last_active_index')) {
                $table->index('last_active', 'users_last_active_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_user_id_index');
            $table->dropIndex('posts_is_private_index');
            $table->dropIndex('posts_created_at_index');
        });

        Schema::table('follows', function (Blueprint $table) {
            $table->dropIndex('follows_follower_id_index');
            $table->dropIndex('follows_followed_id_index');
        });

        Schema::table('stories', function (Blueprint $table) {
            $table->dropIndex('stories_user_id_index');
            $table->dropIndex('stories_expires_at_index');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_post_id_index');
            $table->dropIndex('comments_user_id_index');
        });

        Schema::table('likes', function (Blueprint $table) {
            $table->dropIndex('likes_post_id_index');
            $table->dropIndex('likes_user_id_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_is_online_index');
            $table->dropIndex('users_last_active_index');
        });
    }
};
