<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        // The expo is not a continuous multi-day block: it runs a fixed
        // window of working hours on each of its days. Storing the date, the
        // daily window and the organiser's timezone separately lets every
        // figure be reported in local time and clipped to opening hours.
        $timezone = SiteSetting::get('expo_timezone') ?: 'Asia/Kuala_Lumpur';
        SiteSetting::set('expo_timezone', $timezone);

        if (! SiteSetting::get('expo_start_date')) {
            $legacy = SiteSetting::get('expo_starts_at');

            $startDate = $legacy
                ? Carbon::parse($legacy)->setTimezone($timezone)->toDateString()
                : Carbon::now($timezone)->toDateString();

            SiteSetting::set('expo_start_date', $startDate);
        }

        if (! SiteSetting::get('expo_days')) {
            SiteSetting::set('expo_days', 3);
        }

        if (! SiteSetting::get('expo_daily_start')) {
            SiteSetting::set('expo_daily_start', '15:00');
        }

        if (! SiteSetting::get('expo_daily_end')) {
            SiteSetting::set('expo_daily_end', '19:00');
        }
    }

    public function down(): void
    {
        foreach (['expo_timezone', 'expo_start_date', 'expo_daily_start', 'expo_daily_end'] as $key) {
            SiteSetting::set($key, null);
        }
    }
};
