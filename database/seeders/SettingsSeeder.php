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
            // Keep direct db:seed runs compatible with the specialty-aware UI.
            ['key' => 'clinic_specialty', 'value' => 'general_practice', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            if ($setting['key'] === 'clinic_specialty') {
                Setting::firstOrCreate(['key' => $setting['key']], $setting);
                continue;
            }

            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
