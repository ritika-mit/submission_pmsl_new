<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;

class SendScheduledTestEmail extends Command
{
    protected $signature = 'email:test-scheduled';
    protected $description = 'Send a scheduled test email';

    public function handle()
    {
        Mail::to('h.mittal159@gmail.com')->send(new TestEmail()); // your email
        Log::info("Scheduled test email sent");
        $this->info('Scheduled test email sent at ' . now()->setTimezone('Asia/Kolkata')->toDateTimeString() . ' IST');

    }
}
