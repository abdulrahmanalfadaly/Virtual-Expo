<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('is_published');
        });

        // Backfill: schools that already existed before this feature was added
        // are treated as already approved, so they read as "Unpublished" (not
        // "Pending Approval") if an admin currently has them turned off.
        \App\Models\School::query()->update(['approved_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('approved_at');
        });
    }
};
