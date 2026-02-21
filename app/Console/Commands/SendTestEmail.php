<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    protected $signature = 'mail:test {email?}';

    protected $description = 'Send a test email to verify email configuration';

    public function handle()
    {
        $email = $this->argument('email') ?? 'lptp1563@gmail.com';
        
        $this->info("Sending test email to: {$email}");

        try {
            // Create a fake user for testing
            $user = new \stdClass();
            $user->name = 'Test User';
            $user->email = $email;
            
            $verificationCode = '123456';

            Mail::send('emails.verification-code', ['user' => $user, 'verificationCode' => $verificationCode], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test Email - Laravel Social');
            });

            $this->info('Test email sent successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
            return 1;
        }
    }
}
