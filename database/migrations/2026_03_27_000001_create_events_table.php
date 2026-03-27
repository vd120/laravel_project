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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('event_type'); // new_job, graduation, engagement, baby, moved, birthday
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->year('year')->nullable(); // For anniversaries
            $table->boolean('is_anniversary')->default(false);
            $table->boolean('is_private')->default(false);
            $table->string('badge_icon')->nullable(); // Custom emoji/icon for the event
            $table->json('metadata')->nullable(); // Additional data (company name, school, etc.)
            $table->timestamps();

            $table->index(['user_id', 'event_type']);
            $table->index(['event_date', 'is_anniversary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
