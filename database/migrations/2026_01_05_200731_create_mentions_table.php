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
        Schema::create('mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentioner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mentioned_id')->constrained('users')->onDelete('cascade');
            $table->morphs('mentionable'); 
            $table->timestamps();

            
            $table->index(['mentioner_id', 'mentioned_id']);
            $table->unique(['mentioner_id', 'mentioned_id', 'mentionable_type', 'mentionable_id'], 'unique_mention'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentions');
    }
};
