<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeleteOldManuscriptFiles_copyNew extends Command
{
    protected $signature = 'manuscripts:delete-old-files-copy-new {--dry-run}';
    protected $description = 'Delete files of rejected/withdrawn/deleted manuscripts and all their old revisions (older than 30 days)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $now = Carbon::now();
        $logFileName = 'deleted_manuscript_files_' . $now->format('Y_m_d') . '.log';
        $logPath = storage_path('logs/' . $logFileName);

        $this->info($dryRun ? '[Dry Run] Starting cleanup...' : 'Starting cleanup...');
        if ($dryRun) {
            $this->info("Log will not be saved in dry run.");
        }

        // Step 1: Get manuscript IDs that have at least one old rejected/withdrawn/deleted revision
        $targetManuscriptIds = DB::table('revisions')
            ->whereIn('status', ['rejected', 'withdrawn', 'deleted'])
            ->where('updated_at', '<', Carbon::now()->subDays(30))
            ->pluck('manuscript_id')
            ->unique()
            ->toArray();

        if (empty($targetManuscriptIds)) {
            $this->info('No qualifying revisions found.');
            return;
        }

        // Step 2: Get all old revisions for those manuscript IDs (excluding ones already nullified)
        $revisions = DB::table('revisions')
            ->whereIn('manuscript_id', $targetManuscriptIds)
            ->where(function ($query) {
                $query->whereNotNull('source_file')
                      ->orWhereNotNull('anonymous_file');
            })
            ->get();

        foreach ($revisions as $revision) {
            $files = [
                'source_file' => $revision->source_file,
                'anonymous_file' => $revision->anonymous_file,
            ];

            foreach ($files as $field => $path) {
                if ($path && Storage::disk('local')->exists($path)) {
                    if ($dryRun) {
                        $this->info("[Dry Run] Would delete: {$path}");
                    } else {
                        try {
                            Storage::disk('local')->delete($path);
                            $this->info("Deleted: {$path}");

                            // Log the deletion
                            file_put_contents(
                                $logPath,
                                "Deleted {$field}: {$path} for Manuscript ID {$revision->manuscript_id}\n",
                                FILE_APPEND
                            );
                        } catch (\Exception $e) {
                            Log::error("Error deleting {$path}: " . $e->getMessage());
                        }
                    }
                }
            }

            if (!$dryRun) {
                DB::table('revisions')
                    ->where('id', $revision->id)
                    ->update(['source_file' => null, 'anonymous_file' => null]);
            } else {
                $this->info("[Dry Run] Would nullify source_file and anonymous_file for Revision ID {$revision->id}");
            }
        }

        // Step 3: Process manuscripts (only if copyright_form is not already null)
        $manuscripts = DB::table('manuscripts')
            ->whereIn('id', $targetManuscriptIds)
            ->whereNotNull('copyright_form')
            ->get();

        foreach ($manuscripts as $manuscript) {
            $copyrightPath = $manuscript->copyright_form;

            if ($copyrightPath && Storage::disk('local')->exists($copyrightPath)) {
                if ($dryRun) {
                    $this->info("[Dry Run] Would delete: {$copyrightPath}");
                } else {
                    try {
                        Storage::disk('local')->delete($copyrightPath);
                        $this->info("Deleted: {$copyrightPath}");

                        // Log the deletion
                        file_put_contents(
                            $logPath,
                            "Deleted copyright_form: {$copyrightPath} for Manuscript ID {$manuscript->id}\n",
                            FILE_APPEND
                        );
                    } catch (\Exception $e) {
                        Log::error("Error deleting {$copyrightPath}: " . $e->getMessage());
                    }
                }
            }

            if (!$dryRun) {
                DB::table('manuscripts')
                    ->where('id', $manuscript->id)
                    ->update(['copyright_form' => null]);
            } else {
                $this->info("[Dry Run] Would nullify copyright_form for Manuscript ID {$manuscript->id}");
            }
        }

        $this->info($dryRun ? '[Dry Run] Cleanup simulated.' : 'Cleanup completed.');
        Log::info('Old manuscript and revision files cleanup process finished.', ['dryRun' => $dryRun]);
    }
}
