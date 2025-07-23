<?php

namespace App\Notifications\Manuscript;

use App\Models\Manuscript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewSubmitted extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->cc('noreply@ijmems.in')
            ->template('notifications.email')
            ->subject("Thanks for reviewing the manuscript for International Journal of Mathematical, Engineering and Management Sciences")
            ->greeting(implode(' ', array_filter([$notifiable->title, $notifiable->name])))
            ->line('Thank you for reviewing the manuscript for the International Journal of Mathematical, Engineering and Management Sciences. We greatly appreciate the voluntary contribution that you give to the Journal and hope that we may continue to seek your assistance with the refereeing process for the International Journal of Mathematical, Engineering and Management Sciences (IJMEMS).')
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
