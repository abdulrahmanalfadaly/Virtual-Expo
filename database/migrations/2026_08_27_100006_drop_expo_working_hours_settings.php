<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // The expo now runs round the clock: an expo day is a full 24 hours,
        // so the daily opening window no longer applies.
        SiteSetting::set('expo_daily_start', null);
        SiteSetting::set('expo_daily_end', null);
    }

    public function down(): void
    {
        SiteSetting::set('expo_daily_start', '15:00');
        SiteSetting::set('expo_daily_end', '19:00');
    }
};
