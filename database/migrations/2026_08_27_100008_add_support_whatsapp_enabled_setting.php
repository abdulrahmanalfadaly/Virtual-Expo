<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Default to visible so the button keeps behaving as it does today
        // until an admin deliberately turns it off.
        if (SiteSetting::get('support_whatsapp_enabled') === null) {
            SiteSetting::set('support_whatsapp_enabled', true);
        }
    }

    public function down(): void
    {
        SiteSetting::set('support_whatsapp_enabled', null);
    }
};
