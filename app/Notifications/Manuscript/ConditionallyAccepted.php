<?php

namespace App\Notifications\Manuscript;

use App\Models\Manuscript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConditionallyAccepted extends Notification implements ShouldQueue
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
            ->subject('Your manuscript is conditionally accepted - International Journal of Mathematical, Engineering and Management Sciences')
            ->greeting(implode(' ', array_filter([$notifiable->title, $notifiable->name, ', WITH CC TO ALL AUTHORS'])))
            ->line('We are pleased to inform you that your manuscript has been ***conditionally accepted*** for publication in the International Journal of Mathematical, Engineering and Management Sciences. Final acceptance will be done after receiving the Article Processing Charges (APCs).')
            ->line('In the coming days, we will inform you about the Article Processing Charges (APCs) through any one of the e-mail id [billing@ijmems.in](billing@ijmems.in) [eicijmems@gmail.com](eicijmems@gmail.com) [eicijmems@ijmems.in](eicijmems@ijmems.in). Kindly note that in case of non-receiving the Article Processing Charges (APCs), your manuscript will not be published in IJMEMS.')
            ->line('After receiving the Article Processing Charges (APCs), you will receive the proof of your paper.')
            ->line("***Manuscript ID***: {$this->manuscript->code}")
            ->line("***Manuscript Title***: {$this->manuscript->revision->title}")
            ->line("***Abstract***: {$this->manuscript->revision->abstract}")
            ->line("***Keywords***: {$this->manuscript->revision->keywords}")
            ->line('Kindly consider the International Journal of Mathematical, Engineering and Management Sciences for your future manuscripts.')
            ->line('Kindly cite International Journal of Mathematical, Engineering and Management Sciences published papers in your future research.');
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
