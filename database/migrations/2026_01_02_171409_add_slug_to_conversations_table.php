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
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('slug', 32)->unique()->after('id');
        });

        // Generate slugs for existing conversations
        $conversations = \DB::table('conversations')->get();
        foreach ($conversations as $conversation) {
            \DB::table('conversations')
                ->where('id', $conversation->id)
                ->update(['slug' => Str::random(24)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
