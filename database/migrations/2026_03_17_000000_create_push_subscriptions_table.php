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
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('endpoint'); // Push service endpoint
            $table->string('p256dh'); // User public key
            $table->string('auth'); // User auth secret
            $table->string('content_encoding')->default('aesgcm'); // Content encoding
            $table->json('settings')->nullable(); // Notification preferences
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            // Index for faster lookups (using prefix for text field)
            $table->index('user_id');
            $table->index('endpoint', null, 255); // Index only first 255 chars
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
