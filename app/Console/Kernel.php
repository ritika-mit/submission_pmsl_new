<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('manuscript:invite-reviewer')->everyMinute()->appendOutputTo(storage_path('logs/invite-reminder.log-' . now()->format('Y-m-d') . '.log'));
        $schedule->command('manuscript:remind-reviewer')->everyMinute()->appendOutputTo(storage_path('logs/review-reminder-' . now()->format('Y-m-d') . '.log'));
        $schedule->command('manuscripts:delete-old-files-copy-new')->daily()->withoutOverlapping();
        $schedule->command('email:test-scheduled')->everyMinute()->appendOutputTo(storage_path('logs/test-mails-' . now()->format('Y-m-d') . '.log'));

        //$schedule->command('email:test-scheduled')->everyMinute(); // or any time you want

    }

    /**
     * Register the commands for the application.
     */
     
    
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
