<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔐 Creating admin user...');

        // Create admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'is_admin' => true,
                'last_active' => now(),
                'is_online' => true,
            ]
        );

        // Update admin if already exists but missing fields
        if (!$admin->username) {
            $admin->update(['username' => 'admin']);
        }

        // Create profile for admin if it doesn't exist
        $admin->profile()->firstOrCreate(
            [],
            [
                'bio' => 'System Administrator | Managing the platform',
                'is_private' => false,
                'avatar' => null,
            ]
        );

        $this->command->info('✓ Admin user created/updated successfully!');
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  ADMIN CREDENTIALS');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  Email:    admin@example.com');
        $this->command->info('  Username: admin');
        $this->command->info('  Password: admin123');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
    }
}
