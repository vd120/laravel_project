<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:vapid-generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate VAPID keys for web push notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating VAPID keys for web push notifications...');
        $this->newLine();

        try {
            $keys = VAPID::createVapidKeys();

            $publicKey = $keys['publicKey'];
            $privateKey = $keys['privateKey'];

            $this->info('✓ VAPID keys generated successfully!');
            $this->newLine();

            $this->line('Add these lines to your .env file:');
            $this->newLine();

            $this->line('<fg=green>VAPID_PUBLIC_KEY=' . $publicKey . '</>');
            $this->line('<fg=green>VAPID_PRIVATE_KEY=' . $privateKey . '</>');
            $this->line('<fg=green>VAPID_SUBJECT=mailto:noreply@' . config('app.url') . '</>');
            $this->newLine();

            $this->warn('⚠ Important: Keep your VAPID_PRIVATE_KEY secret!');
            $this->warn('⚠ Never commit your .env file to version control.');
            $this->newLine();

            $this->line('After adding these to your .env file, run:');
            $this->line('<fg=cyan>php artisan config:clear</>');
            $this->newLine();

            $this->info('Done! Push notifications are now configured.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error generating VAPID keys: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
