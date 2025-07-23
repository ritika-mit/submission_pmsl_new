<?php

namespace App\Notifications\Manuscript;

use App\Models\Manuscript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RevisionReminder extends Notification implements ShouldQueue
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
     * This will send the reminder email to the author to submit the revised version of the Article
     * Date: 28-09-2023
     */
    public function toMail(object $notifiable): MailMessage
    {
        $this->manuscript->loadMissing('authors.author');
        $co_authors = $this->manuscript->authors->map->author;

        return (new MailMessage)
            ->cc($co_authors->pluck('email', 'name'))
            ->cc('noreply@ramartipublishers.com')
            ->template('notifications.email')
            ->subject('PMSL-Kindly submit your revised paper with replies of reviewers & editor\'s comments')
            ->greeting(implode(' ', array_filter([$notifiable->title, $notifiable->name, ', WITH CC TO ALL AUTHORS'])))
            ->line("We hope you are fine and doing well.")
            ->line("Further, kindly submit the revised version of your manuscript within 15 days. If you require more time, please inform to the Editor-in-Chief at the email id <eicijmems@ijmems.in>")
            ->line("Kindly login to your account at https://submissionpmsl.ramartipublishers.com/.  After login, follow the below steps to complete the revision process:")
            ->line("Click on the \"Under Revision\" tab (left-hand side links)")
            ->line("Follow the instructions provided under the \"Action\" button.")
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
