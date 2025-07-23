<?php

namespace App\Notifications\Manuscript;

use App\Models\Manuscript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Withdrawn extends Notification implements ShouldQueue
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
            ->subject('Your manuscript is withdrawn - International Journal of Mathematical, Engineering and Management Sciences')
            ->greeting(implode(' ', array_filter([$notifiable->title, $notifiable->name, ', WITH CC TO ALL AUTHORS'])))
            ->line('Thank you for considering IJMEMS as a venue for your manuscript.')
            ->line('Publishing in IJMEMS is becoming increasingly competitive, as there is limited space in the journal.')
            ->line('With this in view, we have to prioritize papers that we publish and some good quality articles sometimes cannot be accommodated. We are unable to consider your paper for publication (paper/article has been withdrawn and you\'re free to submit it at any other place) at this time because of the reason below:')
            ->line('IJMEMS receives many submissions and we have to prioritize to publish multidisciplinary mathematical, engineering & management research [(https://ijmems.in/aimsandscope.php)](https://ijmems.in/aimsandscope.php) addressing clearly defined scientific questions of broad readership interest and impact.')
            ->line('This decision has nothing to do with the novelty or quality of the described work.')
            ->line('As such, we formally release you to submit your work to a more appropriate journal on time. We know you will be disappointed by this response but we should nevertheless like to thank you for considering IJMEMS as a location for your work.')
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
