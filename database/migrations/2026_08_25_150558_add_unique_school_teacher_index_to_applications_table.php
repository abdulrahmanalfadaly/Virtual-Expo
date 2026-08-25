<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Collapse any existing duplicate (school, teacher) application pairs down to
        // the most recent one before the unique index is added, so the migration
        // doesn't fail on data that predates this constraint.
        $duplicateGroups = DB::table('applications')
            ->select('school_id', 'teacher_id')
            ->whereNotNull('teacher_id')
            ->groupBy('school_id', 'teacher_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $rows = DB::table('applications')
                ->where('school_id', $group->school_id)
                ->where('teacher_id', $group->teacher_id)
                ->orderByDesc('id')
                ->get();

            foreach ($rows->skip(1) as $row) {
                if ($row->cv_path) {
                    Storage::disk('local')->delete($row->cv_path);
                }
                DB::table('applications')->where('id', $row->id)->delete();
            }
        }

        Schema::table('applications', function (Blueprint $table) {
            $table->unique(['school_id', 'teacher_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'teacher_id']);
        });
    }
};
