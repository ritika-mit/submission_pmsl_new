<?php

namespace App\Notifications\Manuscript;

use App\Models\Manuscript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewReminder extends Notification implements ShouldQueue
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
        $home = route('index');

        return (new MailMessage)
            ->cc('noreply@ijmems.in')
            ->template('notifications.email')
            ->subject('Friendly Reminder: International Journal of Mathematical, Engineering and Management Sciences Manuscript Reviewer Invitation')
            ->greeting(implode(' ', array_filter([$notifiable->title, $notifiable->name])))
            ->line('We hope you are fine and doing well.')
            ->line('Further, kindly complete the review of the manuscript.')
            ->line('As you know, timely reviews are very important to the authors as well as to the journal\'s reputation in the research community, therefore it is requested you please submit the review report of the article which was assigned to you. We know you are very busy, but we are confident that you will help us.  ')
            ->line("Kindly log in to your account at [{$home}]({$home}). After login, follow the below steps to complete the review process:")
            ->line('*   Click on the "Reviewer" tab (near the top of the window)')
            ->line('*   Click on the "Review Manuscripts" (left-hand side links)')
            ->line('*   Click on the "Review" button.')
            ->line('*   Here, give your comments/suggestions in the space provided in the pop-up window and click on the "Submit Review" button.')
            ->line("***Manuscript ID***: {$this->manuscript->code}")
            ->line("***Manuscript Title***: {$this->manuscript->revision->title}")
            ->line("***Abstract***: {$this->manuscript->revision->abstract}")
            ->line("***Keywords***: {$this->manuscript->revision->keywords}")
            ->line('Many thanks again for your support of the journal.');
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
