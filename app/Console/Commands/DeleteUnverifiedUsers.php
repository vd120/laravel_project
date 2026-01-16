<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class DeleteUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:delete-unverified {--hours=24 : Number of hours to wait before deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete users who haven\'t verified their email within the specified hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');

        $this->info("Deleting unverified users older than {$hours} hours...");

        // Find unverified users older than the specified hours
        $cutoffDate = Carbon::now()->subHours($hours);

        $unverifiedUsers = User::whereNull('email_verified_at')
            ->where('created_at', '<', $cutoffDate)
            ->get();

        $count = $unverifiedUsers->count();

        if ($count === 0) {
            $this->info('No unverified users found to delete.');
            return;
        }

        $this->warn("Found {$count} unverified users older than {$hours} hours:");

        foreach ($unverifiedUsers as $user) {
            $this->line("- {$user->name} ({$user->email}) - Created: {$user->created_at->diffForHumans()}");
        }

        if ($this->confirm("Do you want to delete these {$count} users?")) {
            foreach ($unverifiedUsers as $user) {
                $user->delete();
                $this->line("✓ Deleted: {$user->name} ({$user->email})");
            }

            $this->info("Successfully deleted {$count} unverified users.");
        } else {
            $this->info('Operation cancelled.');
        }
    }
}
