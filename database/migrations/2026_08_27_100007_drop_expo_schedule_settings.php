<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // The platform is no longer a fixed-length dated event, so the expo
        // schedule no longer means anything. The reporting timezone stays.
        SiteSetting::set('expo_start_date', null);
        SiteSetting::set('expo_days', null);
    }

    public function down(): void
    {
        // Nothing to restore — the schedule concept has been removed.
    }
};
