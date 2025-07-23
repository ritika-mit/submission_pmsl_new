<?php

namespace App\Listeners;

use App\Models\GuestAuthor;
use App\Models\RevisionAuthor;
use App\Models\RevisionReviewer;
use Illuminate\Auth\Events\Verified;

class UpdateAuthorForeignKeyReference
{
    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        if (!($author = $event->user)) return;

        $guest_author = GuestAuthor::query()
            ->whereEmail($author->email)
            ->first();

        if (!$guest_author) return;

        RevisionReviewer::query()
            ->whereReviewerType($guest_author::class)
            ->whereReviewerId($guest_author->getKey())
            ->update([
                'reviewer_type' => $author::class,
                'reviewer_id' => $author->getKey(),
            ]);

        RevisionAuthor::query()
            ->whereAuthorType($guest_author::class)
            ->whereAuthorId($guest_author->getKey())
            ->update([
                'author_type' => $author::class,
                'author_id' => $author->getKey(),
            ]);

        $guest_author->delete();
    }
}
