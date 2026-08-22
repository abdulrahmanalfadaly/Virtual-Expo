<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSchoolsSeeder extends Seeder
{
    public function run(): void
    {
        $demoSchools = [
            [
                'name' => 'Riverside International Academy',
                'color' => '#2563eb',
                'short' => 'A globally-minded IB curriculum school nurturing confident, creative learners.',
                'programs' => ['International Baccalaureate', 'STEM Enrichment', 'Creative Arts'],
            ],
            [
                'name' => 'Maple Grove High School',
                'color' => '#16a34a',
                'short' => 'Public high school known for strong athletics and a supportive community.',
                'programs' => ['Advanced Placement', 'Varsity Athletics', 'Student Leadership'],
            ],
            [
                'name' => 'Horizon Technical Institute',
                'color' => '#ea580c',
                'short' => 'Career-focused technical education preparing students for tomorrow\'s industries.',
                'programs' => ['Software Development', 'Robotics & Engineering', 'Digital Media'],
            ],
        ];

        foreach ($demoSchools as $i => $demo) {
            $email = 'demo.school'.($i + 1).'@example.com';

            if (User::where('email', $email)->exists()) {
                continue;
            }

            $user = User::create([
                'name' => 'Demo Admissions Team',
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'school',
            ]);

            $school = School::create([
                'user_id' => $user->id,
                'name' => $demo['name'],
                'slug' => School::uniqueSlug($demo['name']),
                'contact_person' => 'Demo Admissions Team',
                'theme_color' => $demo['color'],
                'short_description' => $demo['short'],
                'full_description' => $demo['short'].' This is placeholder demo content — replace or remove before going live.',
                'contact_email' => $email,
                'is_published' => true,
                'status' => 'active',
            ]);

            foreach ($demo['programs'] as $index => $title) {
                $school->programs()->create([
                    'title' => $title,
                    'description' => 'Placeholder demo program description.',
                    'sort_order' => $index,
                ]);
            }
        }
    }
}
