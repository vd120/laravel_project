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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('avatar')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('bio')->nullable();
            $table->string('website')->nullable();
            $table->string('location')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('occupation')->nullable();
            $table->text('about')->nullable();
            $table->string('phone')->nullable();
            $table->string('gender')->nullable();
            $table->json('social_links')->nullable(); // For multiple social media links
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
