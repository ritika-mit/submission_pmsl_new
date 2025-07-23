namespace App\Console\Commands\Manuscript;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;

class SendScheduledTestEmail extends Command
{
    protected $signature = 'email:test-scheduled-org';
    protected $description = 'Send a scheduled test email';

    public function handle()
    {
        Mail::to('h.mittal159@gmail.com')->send(new TestEmail()); // put your email here
        $this->info('Scheduled test email sent!');
    }
}
