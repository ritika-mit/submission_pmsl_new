<?php

namespace App\Notifications\Manuscript;

use App\Models\Manuscript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssociateEditorAssigned extends Notification implements ShouldQueue
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
            ->subject("International Journal of Mathematical, Engineering and Management Sciences has assigned a manuscript to you as Associate Editor")
            ->greeting(implode(' ', array_filter([$notifiable->title, $notifiable->name])))
            ->line("The manuscript (***{$this->manuscript->code}***), entitled ***{$this->manuscript->revision->title}*** corresponding author {$this->manuscript->author->name} has been assigned to you and is in your Associate Editor Account, awaiting reviewer selection.")
            ->line("Please select reviewers by ONE WEEK TIME.")
            ->line("You can access your Associate Editor Account at IJMEMS submission system:")
            ->action("Click here to login", route('auth.index'))
            ->line("Please note that you will need to provide your username and password when using this link to access the manuscript.")
            ->line("Your username is {$notifiable->email}")
            ->line("After selecting a reviewer for a manuscript, you will need to click on the \"***Invite***\" button to send an invitation email to the reviewer.")
            ->line("If, for any reason, you are unable to serve as Associate Editor for this manuscript, please notify us immediately at <eicijmems@ijmems.in> so that I can reassign it.");
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
