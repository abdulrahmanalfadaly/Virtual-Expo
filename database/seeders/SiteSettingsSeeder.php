<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_name' => 'Virtual School Expo',
            'expo_title' => 'Virtual School Expo',
            'hero_headline' => 'Discover Your Future School, All in One Place',
            'hero_description' => 'Explore interactive booths from leading schools, chat with admissions teams, and find the right fit for your child — all from the comfort of home.',
            'about_content' => "The Virtual School Expo brings together schools and prospective families in one welcoming online space. Browse booths, watch videos, review programs, and apply directly — no travel required.",
            'contact_email' => 'info@example.com',
            'contact_phone' => '',
            'contact_address' => '',
            'support_info' => 'Need help? Reach out to our support team and we will get back to you shortly.',
            'footer_text' => '© '.date('Y').' Virtual School Expo. All rights reserved.',
            'expo_logo_path' => null,
            'site_background_path' => null,
            'hero_overlay_opacity' => 70,
            'allow_registration' => true,
            'allow_applications' => true,
            'booth_template_path' => null,
            'booth_logo_x' => 50,
            'booth_logo_y' => 71.5,
            'booth_logo_width' => 48,
            'booth_logo_max_height' => 13,
            'booth_name_curve' => 120,
            'booth_name_x' => 50,
            'booth_name_y' => 7.3,
            'booth_grid_columns' => 2,
            'booth_grid_gap' => 2.5,
            'booth_modal_opacity' => 90,
        ];

        foreach ($defaults as $key => $value) {
            if (SiteSetting::where('key', $key)->doesntExist()) {
                SiteSetting::set($key, $value);
            }
        }
    }
}
