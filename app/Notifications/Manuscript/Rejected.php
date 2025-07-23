<?php

namespace App\Notifications\Manuscript;

use App\Models\Manuscript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Rejected extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Manuscript $manuscript)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $this->manuscript->loadMissing('authors.author');
        $co_authors = $this->manuscript->authors->map->author;

        return (new MailMessage)
            ->cc($co_authors->pluck('email', 'name'))
            ->cc('noreply@ramartipublishers.com')
            ->template('notifications.email')
            ->subject('Your manuscript is rejected - Prabha Materials Science Letters')
            ->greeting(implode(' ', array_filter([$notifiable->title, $notifiable->name, ', WITH CC TO ALL AUTHORS'])))
            ->line('We are writing to you in regards to the above subjected manuscript which you have submitted to the Prabha Materials Science Letters.')
            ->line('In view of the reviewers\' report(s) found in your authors account at PMSL Submission System, your manuscript has been denied for publication.')
            ->line('Thank you for considering the Prabha Materials Science Letters for the consideration of your research. We hope the outcome of this specific submission will not be discourage you from the submission of future manuscripts.')
            ->line("***Manuscript ID***: {$this->manuscript->code}")
            ->line("***Manuscript Title***: {$this->manuscript->revision->title}")
            ->line("***Abstract***: {$this->manuscript->revision->abstract}")
            ->line("***Keywords***: {$this->manuscript->revision->keywords}");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
