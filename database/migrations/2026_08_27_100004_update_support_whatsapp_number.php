<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const PREVIOUS = '+60194853195';

    private const CURRENT = '+60177245793';

    public function up(): void
    {
        $stored = SiteSetting::get('support_whatsapp');

        // Only replace the superseded default. If an admin has since set some
        // other number through the settings screen, that choice wins.
        if ($stored === null || $stored === '' || $stored === self::PREVIOUS) {
            SiteSetting::set('support_whatsapp', self::CURRENT);
        }
    }

    public function down(): void
    {
        if (SiteSetting::get('support_whatsapp') === self::CURRENT) {
            SiteSetting::set('support_whatsapp', self::PREVIOUS);
        }
    }
};
