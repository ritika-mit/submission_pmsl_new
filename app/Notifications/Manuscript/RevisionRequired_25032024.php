<?php

namespace App\Notifications\Manuscript;

use App\Models\Manuscript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RevisionRequired extends Notification implements ShouldQueue
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
            ->greeting(implode(' ', array_filter([$notifiable->title, $notifiable->name, ', WITH CC TO ALL AUTHORS'])))
            ->line("We have received the review reports from our advisors on your manuscript. Based on the advice received, we feel that your manuscript could be reconsidered for publication should you be prepared to incorporate reviewer comments/editor's comments. When preparing your revised manuscript, you are asked to carefully consider the reviewer comments/editor's comments, ***which can be found in \"Reviewer Comments\" clicking on \"Review Comments\"*** and submit a list of responses (write a reply to each comment) to the comments with your revised manuscript. Editor's Common Comments can be downloaded from this [link](https://www.ijmems.in/assets/editor-common-comments.pdf)")
            ->line("***Highlight*** the changes by the ***YELLOW COLOUR*** in your revised manuscript.")
            ->line("***Kindly note that the number of words in the revised manuscript should not be decreased.***")
            ->line("Before the revised manuscript submission, kindly go through the journal Guidelines for Authors at [https://ijmems.in/forauthors.php](https://ijmems.in/forauthors.php) and the latest published issue, very very carefully.")
            ->line("Kindly submit your all materials (all comment replies; anonymous revised manuscript pdf file; revised manuscript in .docx file with author names, conflict of interest and acknowledgement statements) within ***30 days*** after receiving this email, through your login user id at IJMEMS submission system link [https://submission.ijmems.in](https://submission.ijmems.in)")
            ->line("After login, you have to click the \"Under Revision\" tab in left-hand side links, then click \"Revise\" under the \"Action\" tab, and then follow the instructions given on the screen to upload the revised manuscript.")
            ->line("The revised submission is not the guarantee of acceptance of your article. It will depend on \"How correctly authors have applied the reviewers and editors suggestions?\"")
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
