<?php

namespace App\Console\Commands;

use App\Enums\Manuscript\Status;
use App\Models\RevisionReviewer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;


class InviteRevisionReviewer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manuscript:invite-reviewer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invite Revision Reviewer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        RevisionReviewer::query()
            ->with('reviewer', 'revision.manuscript')
            ->whereNotNull('invited_at')
            ->whereNull('accepted_at')
            ->whereNull('denied_at')
            ->whereRaw('IFNULL(`invite_count`, 0) < ?', 2)
            ->whereRaw('DATE_ADD(`invited_at`, INTERVAL IFNULL(`invite_count`, 1) * ? DAY) < CURDATE()', 5)
            ->whereHas('revision', fn ($query) => $query
                ->rightJoin('manuscripts', 'manuscripts.revision_id', '=', 'revisions.id')
                ->whereStatus(Status::SUBMITTED))
            ->get()
            ->each(function ($revision_reviewer) {
                $revision_reviewer->invite();
                $reviewerId = $revision_reviewer->reviewer_id;


                $revision_reviewer->reviewer->sendManuscriptReviewerInvitedNotification(
                    $revision_reviewer->revision->manuscript
                );
                
                $this->info('Reinvite E-mail sent to '. $reviewerId);

            });
            Log::info("Reinvite E-mail sent:");
            $this->info('Reinviteee E-mail sent at ' . now()->setTimezone('Asia/Kolkata')->toDateTimeString() . ' IST');
    }
}
