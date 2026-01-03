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
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('avatar');
            $table->text('about')->nullable()->after('bio');
            $table->string('occupation')->nullable()->after('location');
            $table->string('phone')->nullable()->after('occupation');
            $table->string('gender')->nullable()->after('phone');
            $table->boolean('is_private')->default(false)->after('gender');
            $table->json('social_links')->nullable()->after('is_private');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['cover_image', 'about', 'occupation', 'phone', 'gender', 'is_private', 'social_links']);
        });
    }
};
