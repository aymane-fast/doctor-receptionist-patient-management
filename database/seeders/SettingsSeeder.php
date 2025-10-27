<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Language settings
            ['key' => 'app_language', 'value' => 'en', 'type' => 'string'],
            // Consultation pricing
            ['key' => 'consultation_price', 'value' => '75.00', 'type' => 'decimal'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
