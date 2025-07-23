<?php

namespace App\Notifications\Manuscript;

use App\Models\Manuscript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Revised extends Notification implements ShouldQueue
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
            ->subject('Manuscript revision has been submitted to Prabha Materials Science Letters')
            ->greeting(implode(' ', array_filter([$notifiable->title, $notifiable->name])))
            ->line("Your revised manuscript entitled ***{$this->manuscript->revision->title}*** co-authored {$co_authors->pluck('name')->join(', ')} is received.")
            ->line('Please refer to the manuscript identification number of your manuscript in any correspondence with us.')
            ->line('We assume that this revised manuscript is original, previously unpublished, and not being considered for publication by any other journal or conference. If this is not the case, please let us know immediately.')
            ->line('We will not consider manuscripts that are under review or have been accepted by another journal or conference. Also, the similarity of your manuscript should be below 15%. Kindly note that reviews will be complete in 1-6 months.')
            ->line("***Manuscript ID***: {$this->manuscript->code}")
            ->line("***Manuscript Title***: {$this->manuscript->revision->title}")
            ->line("***Abstract***: {$this->manuscript->revision->abstract}")
            ->line("***Keywords***: {$this->manuscript->revision->keywords}")
            ->line('Kindly consider PMSL for your future publications.');
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
