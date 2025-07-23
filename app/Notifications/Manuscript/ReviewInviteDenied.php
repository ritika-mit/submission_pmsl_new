<?php

namespace App\Notifications\Manuscript;

use App\Models\Manuscript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewInviteDenied extends Notification implements ShouldQueue
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
            ->cc('noreply@ramartipublishers.com')
            ->template('notifications.email')
            ->subject("Thank you for replying to our invitation to review")
            ->greeting(implode(' ', array_filter([$notifiable->title, $notifiable->name])))
            ->line("Thank you for replying to our invitation to review **{$this->manuscript->code}** entitled **{$this->manuscript->revision->title}** for Prabha Materials Science Letters.")
            ->line('It is unfortunate that you are unable to review this manuscript at this time. We shall keep you in mind when future manuscripts come in that fall under your area of expertise.')
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
