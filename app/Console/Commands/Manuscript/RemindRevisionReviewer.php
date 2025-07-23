<?php

namespace App\Console\Commands\Manuscript;

use App\Enums\Manuscript\Status;
use App\Models\RevisionReviewer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RemindRevisionReviewer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manuscript:remind-reviewer-org';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remind revision reviewer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        RevisionReviewer::query()
            ->with('reviewer', 'revision.manuscript')
            ->whereNotNull('accepted_at')
            ->whereRaw('IFNULL(`remind_count`, 0) < ?', 6)
            ->whereRaw('DATE_ADD(`accepted_at`, INTERVAL IFNULL(`remind_count`, 1) * ? DAY) < CURDATE()', 7)
            ->whereHas('revision', fn ($query) => $query
                ->rightJoin('manuscripts', 'manuscripts.revision_id', '=', 'revisions.id')
                ->whereStatus(Status::SUBMITTED))
            ->whereDoesntHave('review', fn ($query) => $query
                ->where('revision_id', DB::raw('`revision_reviewers`.`revision_id`')))
            ->get()
            ->each(function ($revision_reviewer) {
                $revision_reviewer->remind();

                $revision_reviewer->reviewer->sendManuscriptReviewReminderNotification(
                    $revision_reviewer->revision->manuscript
                );
                sleep(500);
            });
            Log::info("Revision.manuscript Reminder E-mail sent:");         
    }

}
