<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // The expo is a fixed-length event; the activity dashboard frames
        // everything against this window. Seeded with a best-guess start so
        // the dashboard is useful immediately — the admin can correct the
        // exact time under Dashboard → General Settings.
        if (! SiteSetting::get('expo_starts_at')) {
            SiteSetting::set('expo_starts_at', now()->subHour()->startOfHour()->toIso8601String());
        }

        if (! SiteSetting::get('expo_days')) {
            SiteSetting::set('expo_days', 3);
        }
    }

    public function down(): void
    {
        SiteSetting::set('expo_starts_at', null);
        SiteSetting::set('expo_days', null);
    }
};
