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
        Schema::table('stories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('user_id');
        });

        // Generate slugs for existing stories
        $stories = \App\Models\Story::all();
        foreach ($stories as $story) {
            if (empty($story->slug)) {
                $story->slug = Str::random(24);
                $story->save();
            }
        }

        // Now make slug unique and required
        Schema::table('stories', function (Blueprint $table) {
            $table->string('slug')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
