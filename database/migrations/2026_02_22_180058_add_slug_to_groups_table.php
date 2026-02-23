<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->string('invite_link')->nullable()->after('slug');
        });

        // Generate slugs for existing groups
        \App\Models\Group::all()->each(function ($group) {
            $group->slug = Str::random(20);
            $group->invite_link = Str::random(24);
            $group->save();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['slug', 'invite_link']);
        });
    }
};