<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'is_admin' => true,
            ]
        );

        // Create profile for admin if it doesn't exist
        if (!$admin->profile) {
            $admin->profile()->create([
                'bio' => 'System Administrator',
                'is_private' => false,
            ]);
        }

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: admin123');
        $this->command->info('Username: admin');
    }
}
