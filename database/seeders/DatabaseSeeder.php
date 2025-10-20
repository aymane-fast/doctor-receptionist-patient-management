<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🏥 Starting comprehensive medical clinic data seeding...');
        
        $this->call([
            UserSeeder::class,          // Creates 1 doctor and 1 receptionist
            PatientSeeder::class,       // Creates 150 patients with realistic data
            SettingsSeeder::class,      // Clinic configuration
            AppointmentSeeder::class,   // Creates appointments spanning 4 weeks
            MedicalRecordSeeder::class, // Creates 220+ medical records over 6 months
            PrescriptionSeeder::class,  // Creates 100 prescriptions with realistic medications
            OrderSeeder::class,         // Creates 50 orders for medication purchases
            PrescriptionItemSeeder::class, // Creates items for each prescription with pricing
        ]);

        $this->command->info('✅ Medical clinic database seeding completed successfully!');
        $this->command->info('📊 Data created:');
        $this->command->info('   - 2 Users (1 doctor, 1 receptionist)');
        $this->command->info('   - 150 Patients with comprehensive profiles');
        $this->command->info('   - 4 weeks of realistic appointment scheduling');
        $this->command->info('   - 220+ Medical records spanning 6 months');
        $this->command->info('   - 100 Prescriptions with detailed medication info');
        $this->command->info('   - 50 Orders with various payment methods');
        $this->command->info('   - Prescription items with realistic pricing');
    }
}
