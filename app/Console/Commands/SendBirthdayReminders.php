<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\User;
use App\Services\EventService;
use Illuminate\Console\Command;

class SendBirthdayReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:send-birthday-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send birthday and anniversary reminder notifications to users';

    protected EventService $eventService;

    /**
     * Execute the console command.
     */
    public function handle(EventService $eventService): int
    {
        $this->eventService = $eventService;

        $this->info('Sending birthday reminders...');
        $this->eventService->sendBirthdayReminders();
        $this->info('Birthday reminders sent!');

        $this->info('Sending anniversary reminders...');
        $this->eventService->sendAnniversaryReminders();
        $this->info('Anniversary reminders sent!');

        $this->info('All reminders processed successfully!');

        return Command::SUCCESS;
    }
}
