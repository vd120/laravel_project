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
        // Generate slugs for existing stories that don't have one
        $stories = \DB::table('stories')->whereNull('slug')->orWhere('slug', '')->get();
        foreach ($stories as $story) {
            \DB::table('stories')->where('id', $story->id)->update([
                'slug' => Str::random(24)
            ]);
        }

        // Make slug required and unique
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
            $table->dropUnique(['slug']);
        });
    }
};
