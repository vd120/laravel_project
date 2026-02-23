<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->boolean('is_group')->default(false)->after('slug');
            $table->foreignId('group_id')->nullable()->after('is_group')->constrained('groups')->onDelete('cascade');
            $table->string('name')->nullable()->after('group_id');
            $table->string('avatar')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn(['is_group', 'group_id', 'name', 'avatar']);
        });
    }
};