<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Seeded so the admin settings field reflects the number the floating
        // WhatsApp button actually dials, rather than sitting empty while the
        // component falls back to its default.
        if (! SiteSetting::get('support_whatsapp')) {
            SiteSetting::set('support_whatsapp', '+60177245793');
        }
    }

    public function down(): void
    {
        SiteSetting::set('support_whatsapp', null);
    }
};
