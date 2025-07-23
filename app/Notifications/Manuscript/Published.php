<?php

namespace App\Notifications\Manuscript;

use App\Models\Manuscript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Published extends Notification implements ShouldQueue
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
            ->subject('Your manuscript is published - Prabha Materials Science Letters')
            ->greeting(implode(' ', array_filter([$notifiable->title, $notifiable->name, ', WITH CC TO ALL AUTHORS'])))
            ->line("It is with great pleasure to inform you that your article has been published in the Prabha Materials Science Letters.")
            ->line('Many thanks for your contribution to PMSL.')
            ->line("***Manuscript ID***: {$this->manuscript->code}")
            ->line("***Manuscript Title***: {$this->manuscript->revision->title}")
            ->line("***Abstract***: {$this->manuscript->revision->abstract}")
            ->line("***Keywords***: {$this->manuscript->revision->keywords}")
            ->line('Kindly consider the Prabha Materials Science Letters for your future manuscripts.')
            ->line('Kindly cite Prabha Materials Science Letters published papers in your future research.');
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
