<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all users without username
        $users = DB::table('users')
            ->whereNull('username')
            ->orWhere('username', '')
            ->get();

        foreach ($users as $user) {
            // Generate username from name
            // Remove all non-alphanumeric characters (spaces, hyphens, etc.)
            $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $user->name));
            
            // If username is empty after removing special chars, use 'user' + id
            if (empty($username)) {
                $username = 'user' . $user->id;
            }
            
            $originalUsername = $username;
            $counter = 1;

            // Ensure uniqueness
            while (DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $originalUsername . $counter;
                $counter++;
            }

            // Update user with generated username
            DB::table('users')
                ->where('id', $user->id)
                ->update(['username' => $username]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally clear usernames (be careful with this in production)
        // DB::table('users')->update(['username' => null]);
    }
};
