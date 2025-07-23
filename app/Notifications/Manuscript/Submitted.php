<?php

namespace App\Notifications\Manuscript;

use App\Models\Manuscript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Submitted extends Notification implements ShouldQueue
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
            ->cc('noreply@ijmems.in')
            ->template('notifications.email')
            ->subject('Manuscript has been submitted to International Journal of Mathematical, Engineering and Management Sciences')
            ->greeting(implode(' ', array_filter([$notifiable->title, $notifiable->name])))
            ->line("Your manuscript entitled ***{$this->manuscript->revision->title}*** co-authored {$co_authors->pluck('name')->join(', ')} is received.")
            ->line('Please refer to the manuscript identification number of your manuscript in any correspondence with us.')
            ->line('We assume that this manuscript is original, previously unpublished, and not being considered for publication by any other journal or conference. If this is not the case, please let us know immediately.')
            ->line('We will not consider manuscripts that are under review or have been accepted by another journal. Also, the similarity of your manuscript should be below 15%. Kindly note that first review will be complete in 1-8 months.')
            ->line("***Manuscript ID***: {$this->manuscript->code}")
            ->line("***Manuscript Title***: {$this->manuscript->revision->title}")
            ->line("***Abstract***: {$this->manuscript->revision->abstract}")
            ->line("***Keywords***: {$this->manuscript->revision->keywords}")
            ->line('Kindly consider IJMEMS for your future publications.');
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
