<?php

namespace App\Console\Commands;

use App\Models\Application;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DedupeApplications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:dedupe-applications {--dry-run : Preview what would be removed without changing anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collapse duplicate CV applications (same school + email, no teacher account) down to the most recent one';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $groups = DB::table('applications')
            ->select('school_id', DB::raw('LOWER(applicant_email) as email_lower'))
            ->whereNull('teacher_id')
            ->groupBy('school_id', 'email_lower')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($groups->isEmpty()) {
            $this->info('No duplicate applications found.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '').'Found '.$groups->count().' duplicate group(s).');
        $this->newLine();

        $totalRemoved = 0;

        foreach ($groups as $group) {
            $rows = Application::where('school_id', $group->school_id)
                ->whereNull('teacher_id')
                ->whereRaw('LOWER(applicant_email) = ?', [$group->email_lower])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();

            $keep = $rows->first();
            $remove = $rows->slice(1);

            $schoolName = $keep->school?->name ?? "#{$group->school_id}";
            $this->line("<fg=cyan>{$group->email_lower}</> at <fg=cyan>{$schoolName}</> — keeping #{$keep->id} ({$keep->created_at->format('M j, Y H:i')}), removing ".$remove->count().' older submission(s)');

            foreach ($remove as $row) {
                $this->line("  - removing application #{$row->id} ({$row->created_at->format('M j, Y H:i')})");

                if (! $dryRun) {
                    if ($row->cv_path) {
                        Storage::disk('local')->delete($row->cv_path);
                    }
                    $row->delete();
                }

                $totalRemoved++;
            }
        }

        $this->newLine();
        $this->info(($dryRun ? 'Would remove ' : 'Removed ').$totalRemoved.' duplicate application(s).');

        if ($dryRun) {
            $this->comment('Run without --dry-run to actually delete these.');
        }

        return self::SUCCESS;
    }
}
