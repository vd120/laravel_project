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
        Schema::table('groups', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });
        
        // Generate slugs for existing groups
        DB::table('groups')->whereNull('slug')->cursor()->each(function ($group) {
            $slug = Str::slug($group->name) . '-' . Str::random(6);
            DB::table('groups')->where('id', $group->id)->update(['slug' => $slug]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};