<?php

namespace App\Notifications\Manuscript;

use App\Enums\Manuscript\Action;
use App\Models\Author;
use App\Models\Manuscript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

class ReviewerInvited extends Notification implements ShouldQueue
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
        $accept_url = URL::signedRoute('manuscripts.respond-review-invite', [
            'manuscript' => $this->manuscript,
            'type' => sha1($notifiable::class),
            'author' => $notifiable,
            'action' => Action::ACCEPT_REVIEW_INVITE,
        ]);

        $deny_url = URL::signedRoute('manuscripts.respond-review-invite', [
            'manuscript' => $this->manuscript,
            'type' => sha1($notifiable::class),
            'author' => $notifiable,
            'action' => Action::DENY_REVIEW_INVITE,
        ]);

        [$action_text, $action_url] = get_class($notifiable) === Author::class
            ? ['Access your account', route('auth.index')]
            : ['Make your account',  URL::signedRoute('auth.invite', ['author' => $notifiable])];

        return (new MailMessage)
            ->template('notifications.email')
            ->subject("International Journal of Mathematical, Engineering and Management Sciences Manuscript Reviewer Invitation")
            ->greeting(implode(' ', array_filter([$notifiable->title, $notifiable->name])))
            ->line("We believe that you would serve as an excellent reviewer (as your name is suggested by some renowned authors) of the manuscript which has been submitted to the International Journal of Mathematical, Engineering and Management Sciences. The abstract of the manuscript is given below for your ready reference. We hope that you will consider undertaking this important task for us.")
            ->line("If you agree to review the above-subjected manuscript, kindly give your consent by clicking the \"***Agree for Review***\" button which is given below (Review time will be 30 days). Also, you will find this manuscript in your account by clicking the \"Reviewer\" tab. Here, you are able to give your comments to the authors and editors to complete the review process.")
            ->action($action_text, $action_url)
            ->line(new HtmlString("<a href=\"{$accept_url}\" class=\"button button-success\">Agree for Review</a>"))
            ->line(new HtmlString("<a href=\"{$deny_url}\" class=\"button button-error\">Deny for Review</a>"))
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
