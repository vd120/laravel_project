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
        Schema::table('post_reports', function (Blueprint $table) {
            $table->enum('admin_action', ['delete', 'hide', 'warning', 'none'])->nullable()->after('admin_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_reports', function (Blueprint $table) {
            $table->dropColumn('admin_action');
        });
    }
};
